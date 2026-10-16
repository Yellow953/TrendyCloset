<?php

namespace App\Support;

use App\Enums\OfferType;
use App\Models\Coupon;
use App\Models\Offer;
use App\Models\ProductVariant;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;

/**
 * The shopper's bag, kept in its own encrypted cookie rather than the database
 * or the session — sessions expire in hours, a bag should survive the evening
 * a shopper spends asking about it on WhatsApp.
 *
 * There is deliberately no `carts` table: an abandoned bag is not a record the
 * CRM wants, and shoppers check out as guests (see the identity split in
 * CLAUDE.md). Only the variant ids + quantities are stored; prices, stock and
 * imagery are re-read from the catalogue on every request so a bag left open
 * for a week can never charge last week's price.
 */
class Cart
{
    public const COOKIE = 'tc_cart';

    /** Thirty days, renewed on every change to the bag. */
    private const LIFETIME_MINUTES = 60 * 24 * 30;

    /** Flat rate — there is no spend threshold that ships free on its own. */
    public const STANDARD_SHIPPING = 4.0;

    /** Per-line safety cap, independent of stock. */
    private const MAX_QTY = 20;

    /** @var Collection<int, array{variant: ProductVariant, qty: int, unit: float, total: float}>|null */
    private ?Collection $lines = null;

    /** @var array{items: array<int, int>, coupon: ?string}|null */
    private ?array $state = null;

    public function __construct(
        private readonly Request $request,
        private readonly Session $session,
    ) {}

    /**
     * Add a variant to the bag, clamped to what is actually in stock.
     * Returns the resulting quantity for that line.
     */
    public function add(ProductVariant $variant, int $qty = 1): int
    {
        $items = $this->rawItems();
        $items[$variant->id] = $this->clamp(($items[$variant->id] ?? 0) + $qty, $variant);

        $this->persist($items);

        return $items[$variant->id];
    }

    /**
     * Set an explicit quantity; a quantity of zero (or less) removes the line.
     */
    public function setQuantity(ProductVariant $variant, int $qty): void
    {
        if ($qty <= 0) {
            $this->remove($variant);

            return;
        }

        $items = $this->rawItems();
        $items[$variant->id] = $this->clamp($qty, $variant);

        $this->persist($items);
    }

    public function remove(ProductVariant $variant): void
    {
        $items = $this->rawItems();
        unset($items[$variant->id]);

        $this->persist($items);
    }

    public function clear(): void
    {
        $this->state = ['items' => [], 'coupon' => null];
        Cookie::queue(Cookie::forget(self::COOKIE));
        $this->lines = null;
        $this->offerResults = null;
    }

    /**
     * The bag as renderable lines, with the catalogue re-joined. Variants that
     * have since been deleted or deactivated silently drop out.
     *
     * @return Collection<int, array{variant: ProductVariant, qty: int, unit: float, total: float}>
     */
    public function lines(): Collection
    {
        if ($this->lines !== null) {
            return $this->lines;
        }

        $items = $this->rawItems();

        if ($items === []) {
            return $this->lines = collect();
        }

        $variants = ProductVariant::with(['product.images', 'product.category'])
            ->whereIn('id', array_keys($items))
            ->where('is_active', true)
            ->get()
            ->filter(fn (ProductVariant $v) => $v->product?->is_active);

        return $this->lines = $variants->map(function (ProductVariant $variant) use ($items) {
            $qty = (int) $items[$variant->id];
            $unit = (float) $variant->effective_price;

            return [
                'variant' => $variant,
                'qty' => $qty,
                'unit' => $unit,
                'total' => round($unit * $qty, 2),
            ];
        })->values();
    }

    public function isEmpty(): bool
    {
        return $this->lines()->isEmpty();
    }

    /** Total number of garments in the bag (not the number of lines). */
    public function count(): int
    {
        return (int) $this->lines()->sum('qty');
    }

    public function subtotal(): float
    {
        return round((float) $this->lines()->sum('total'), 2);
    }

    /**
     * Attach a discount code. Returns false (and stores nothing) when the code
     * is unknown or does not apply to the current subtotal.
     */
    public function applyCoupon(string $code): bool
    {
        $coupon = Coupon::whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))])->first();

        if (! $coupon || ! $coupon->isValidFor($this->subtotal())) {
            return false;
        }

        $this->state()['coupon'] = $coupon->code;
        $this->save();

        return true;
    }

    public function removeCoupon(): void
    {
        $this->state()['coupon'] = null;
        $this->save();
    }

    /**
     * The applied coupon, re-validated against the current subtotal — a code
     * that stops qualifying (because a line was removed) simply stops applying.
     */
    public function coupon(): ?Coupon
    {
        $code = $this->state()['coupon'];

        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();

        return $coupon && $coupon->isValidFor($this->subtotal()) ? $coupon : null;
    }

    public function couponDiscount(): float
    {
        return $this->coupon()?->discountFor($this->subtotal()) ?? 0.0;
    }

    /**
     * The best currently-applicable automatic offer, by discount amount —
     * there is no code to type, so the bag always applies the one that saves
     * the shopper the most rather than stacking every offer that matches.
     */
    public function bestOffer(): ?Offer
    {
        return $this->offerResults()->first()['offer'] ?? null;
    }

    public function offerDiscount(): float
    {
        return (float) ($this->offerResults()->first()['discount'] ?? 0.0);
    }

    /**
     * A coupon code is something the shopper deliberately typed, so it wins a
     * tie; otherwise whichever of a coupon or an automatic offer saves more.
     */
    public function discount(): float
    {
        return max($this->couponDiscount(), $this->offerDiscount());
    }

    /**
     * Which discount is actually driving the total right now, for the bag and
     * checkout to label correctly — null when nothing applies.
     */
    public function discountSource(): ?string
    {
        if ($this->offerDiscount() > $this->couponDiscount()) {
            return 'offer';
        }

        return $this->couponDiscount() > 0 ? 'coupon' : null;
    }

    /** The offer actually driving the discount, only when it beat the coupon. */
    public function appliedOffer(): ?Offer
    {
        return $this->discountSource() === 'offer' ? $this->bestOffer() : null;
    }

    public function shipping(): float
    {
        if ($this->isEmpty() || $this->qualifiesForFreeShipping()) {
            return 0.0;
        }

        return self::STANDARD_SHIPPING;
    }

    /**
     * Free shipping is never automatic — only a coupon or a spend-threshold
     * offer that explicitly grants it, both admin-controlled.
     */
    public function qualifiesForFreeShipping(): bool
    {
        if ((bool) $this->coupon()?->free_shipping) {
            return true;
        }

        $subtotal = $this->subtotal();

        return $this->liveOffers()
            ->contains(fn (Offer $offer) => $offer->type === OfferType::Spend
                && $offer->free_shipping
                && ($offer->min_subtotal === null || $subtotal >= (float) $offer->min_subtotal));
    }

    public function total(): float
    {
        return round($this->subtotal() - $this->discount() + $this->shipping(), 2);
    }

    /**
     * Everything the bag/checkout summaries need, in one call.
     *
     * @return array{subtotal: float, discount: float, shipping: float, total: float, coupon: ?Coupon, offer: ?Offer, free_shipping: bool}
     */
    public function summary(): array
    {
        return [
            'subtotal' => $this->subtotal(),
            'discount' => $this->discount(),
            'shipping' => $this->shipping(),
            'total' => $this->total(),
            'coupon' => $this->coupon(),
            'offer' => $this->appliedOffer(),
            'free_shipping' => $this->qualifiesForFreeShipping(),
        ];
    }

    /** @var Collection<int, Offer>|null */
    private ?Collection $liveOffers = null;

    /** @var Collection<int, array{offer: Offer, discount: float}>|null */
    private ?Collection $offerResults = null;

    /**
     * @return Collection<int, Offer>
     */
    private function liveOffers(): Collection
    {
        return $this->liveOffers ??= Offer::live()->with(['category', 'products'])->get();
    }

    /**
     * Every live offer that actually discounts the current bag, most
     * generous first.
     *
     * @return Collection<int, array{offer: Offer, discount: float}>
     */
    private function offerResults(): Collection
    {
        if ($this->offerResults !== null) {
            return $this->offerResults;
        }

        $lines = $this->lines();
        $subtotal = $this->subtotal();

        return $this->offerResults = $this->liveOffers()
            ->map(fn (Offer $offer) => ['offer' => $offer, 'discount' => $offer->discountFor($lines, $subtotal)])
            ->filter(fn (array $result) => $result['discount'] > 0)
            ->sortByDesc('discount')
            ->values();
    }

    /**
     * @return array<int, int> variant id => quantity
     */
    private function rawItems(): array
    {
        return $this->state()['items'];
    }

    /**
     * The bag as read from the cookie, once per request. A bag still sitting in
     * the session from before the cookie existed is adopted the first time it
     * is read, so nobody's bag empties on deploy.
     *
     * @return array{items: array<int, int>, coupon: ?string}
     */
    private function &state(): array
    {
        if ($this->state === null) {
            $raw = json_decode((string) $this->request->cookie(self::COOKIE), true);
            $legacy = $this->session->pull('tc_cart');

            if (! is_array($raw) && is_array($legacy)) {
                $raw = $legacy;
            }

            $items = [];

            foreach ((array) ($raw['items'] ?? []) as $id => $qty) {
                if ((int) $id > 0 && (int) $qty > 0) {
                    $items[(int) $id] = (int) $qty;
                }
            }

            $this->state = [
                'items' => $items,
                'coupon' => is_string($raw['coupon'] ?? null) ? $raw['coupon'] : null,
            ];

            if (is_array($legacy)) {
                $this->save();
            }
        }

        return $this->state;
    }

    private function save(): void
    {
        $state = $this->state();

        Cookie::queue($state['items'] === [] && $state['coupon'] === null
            ? Cookie::forget(self::COOKIE)
            : Cookie::make(self::COOKIE, json_encode($state), self::LIFETIME_MINUTES, httpOnly: true, sameSite: 'lax'));
    }

    /**
     * @param  array<int, int>  $items
     */
    private function persist(array $items): void
    {
        $this->state()['items'] = $items;
        $this->save();
        $this->lines = null;
        $this->offerResults = null;
    }

    private function clamp(int $qty, ProductVariant $variant): int
    {
        return (int) max(1, min($qty, self::MAX_QTY, max($variant->stock, 1)));
    }
}
