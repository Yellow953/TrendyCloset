<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\ProductVariant;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * The shopper's bag, kept in the session rather than the database.
 *
 * There is deliberately no `carts` table: an abandoned bag is not a record the
 * CRM wants, and shoppers check out as guests (see the identity split in
 * CLAUDE.md). Only the variant ids + quantities are stored; prices, stock and
 * imagery are re-read from the catalogue on every request so a bag left open
 * for a week can never charge last week's price.
 */
class Cart
{
    public const SESSION_KEY = 'tc_cart';

    /** Orders at or above this subtotal ship free. */
    public const FREE_SHIPPING_THRESHOLD = 150.0;

    public const STANDARD_SHIPPING = 9.0;

    /** Per-line safety cap, independent of stock. */
    private const MAX_QTY = 20;

    /** @var Collection<int, array{variant: ProductVariant, qty: int, unit: float, total: float}>|null */
    private ?Collection $lines = null;

    public function __construct(private readonly Session $session) {}

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
        $this->session->forget(self::SESSION_KEY);
        $this->lines = null;
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

        $this->session->put(self::SESSION_KEY.'.coupon', $coupon->code);

        return true;
    }

    public function removeCoupon(): void
    {
        $this->session->forget(self::SESSION_KEY.'.coupon');
    }

    /**
     * The applied coupon, re-validated against the current subtotal — a code
     * that stops qualifying (because a line was removed) simply stops applying.
     */
    public function coupon(): ?Coupon
    {
        $code = $this->session->get(self::SESSION_KEY.'.coupon');

        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();

        return $coupon && $coupon->isValidFor($this->subtotal()) ? $coupon : null;
    }

    public function discount(): float
    {
        return $this->coupon()?->discountFor($this->subtotal()) ?? 0.0;
    }

    public function shipping(): float
    {
        if ($this->isEmpty() || $this->qualifiesForFreeShipping()) {
            return 0.0;
        }

        return self::STANDARD_SHIPPING;
    }

    public function qualifiesForFreeShipping(): bool
    {
        return $this->subtotal() >= self::FREE_SHIPPING_THRESHOLD
            || (bool) $this->coupon()?->free_shipping;
    }

    /** How much more the shopper must spend to ship free (0 once unlocked). */
    public function freeShippingRemainder(): float
    {
        return round(max(0, self::FREE_SHIPPING_THRESHOLD - $this->subtotal()), 2);
    }

    public function total(): float
    {
        return round($this->subtotal() - $this->discount() + $this->shipping(), 2);
    }

    /**
     * Everything the bag/checkout summaries need, in one call.
     *
     * @return array{subtotal: float, discount: float, shipping: float, total: float, coupon: ?Coupon, free_shipping: bool}
     */
    public function summary(): array
    {
        return [
            'subtotal' => $this->subtotal(),
            'discount' => $this->discount(),
            'shipping' => $this->shipping(),
            'total' => $this->total(),
            'coupon' => $this->coupon(),
            'free_shipping' => $this->qualifiesForFreeShipping(),
        ];
    }

    /**
     * @return array<int, int> variant id => quantity
     */
    private function rawItems(): array
    {
        return (array) $this->session->get(self::SESSION_KEY.'.items', []);
    }

    /**
     * @param  array<int, int>  $items
     */
    private function persist(array $items): void
    {
        $this->session->put(self::SESSION_KEY.'.items', $items);
        $this->lines = null;
    }

    private function clamp(int $qty, ProductVariant $variant): int
    {
        return (int) max(1, min($qty, self::MAX_QTY, max($variant->stock, 1)));
    }
}
