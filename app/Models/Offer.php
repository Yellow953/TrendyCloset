<?php

namespace App\Models;

use App\Enums\OfferType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * An automatic, cart-level promotion — no code to type, unlike {@see Coupon}.
 * One table covers three shapes (see {@see OfferType}); which columns matter
 * depends on `type`, the same nullable-column pattern Coupon already uses.
 */
class Offer extends Model
{
    /** @use HasFactory<\Database\Factories\OfferFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'headline',
        'type',
        'discount_type',
        'value',
        'category_id',
        'min_subtotal',
        'free_shipping',
        'buy_qty',
        'get_qty',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => OfferType::class,
            'value' => 'decimal:2',
            'min_subtotal' => 'decimal:2',
            'free_shipping' => 'boolean',
            'buy_qty' => 'integer',
            'get_qty' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, Offer>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Hand-picked products this offer targets instead of a category —
     * CategoryPercent and Bogo only, and never alongside category_id (see
     * {@see qualifyingLines()}, which checks this first).
     *
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * @return HasMany<Order>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @param  Builder<Offer>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Active and inside its date window right now — everything a query needs
     * to shortlist offers before the per-cart discount math runs.
     *
     * @param  Builder<Offer>  $query
     */
    public function scopeLive(Builder $query): void
    {
        $query->active()
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    /**
     * Whether this offer is running right now, independent of any cart.
     */
    public function isLive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Whether this offer badges/mentions an individual product — only the two
     * types that are about specific merchandise rather than the whole cart.
     */
    public function isProductFacing(): bool
    {
        return $this->type === OfferType::CategoryPercent || $this->type === OfferType::Bogo;
    }

    /**
     * Whether the given product falls inside this offer's target — a
     * hand-picked product list when one is set, else the target category
     * (widened to descendants the same way a listing page widens a parent
     * category). A null category on a Bogo with no products scopes the whole
     * catalogue; on a CategoryPercent it never matches (one or the other is
     * required there).
     */
    public function coversProduct(Product $product): bool
    {
        if (! $this->isProductFacing()) {
            return false;
        }

        if ($this->products->isNotEmpty()) {
            return $this->products->contains('id', $product->id);
        }

        if ($this->category_id === null) {
            return $this->type === OfferType::Bogo;
        }

        return in_array($product->category_id, $this->category?->selfAndDescendantIds() ?? [$this->category_id], true);
    }

    /**
     * Discount this offer yields for the given bag (0 when it doesn't apply).
     *
     * @param  Collection<int, array{variant: ProductVariant, qty: int, unit: float, total: float}>  $lines
     */
    public function discountFor(Collection $lines, float $subtotal): float
    {
        if (! $this->isLive()) {
            return 0.0;
        }

        return match ($this->type) {
            OfferType::Spend => $this->spendDiscount($subtotal),
            OfferType::CategoryPercent => $this->categoryPercentDiscount($lines),
            OfferType::Bogo => $this->bogoDiscount($lines),
        };
    }

    /**
     * Customer-facing text for a badge or the header banner — whatever
     * merchandising typed, else one derived from the rule itself.
     */
    public function getDisplayHeadlineAttribute(): string
    {
        if ($this->headline) {
            return $this->headline;
        }

        return match ($this->type) {
            OfferType::Spend => $this->spendHeadline(),
            OfferType::CategoryPercent => $this->percentLabel().' off '.($this->scopeLabel() ?? 'select styles'),
            OfferType::Bogo => 'Buy '.$this->buy_qty.' get '.$this->get_qty.' free'.($this->scopeLabel() ? ' on '.$this->scopeLabel() : ''),
        };
    }

    /**
     * What this offer's category/product scope reads like in a sentence —
     * the hand-picked products when there are any, else the category.
     */
    private function scopeLabel(): ?string
    {
        if ($this->products->isNotEmpty()) {
            return $this->products->count() === 1
                ? $this->products->first()->name
                : $this->products->count().' selected styles';
        }

        return $this->category->name ?? null;
    }

    private function spendHeadline(): string
    {
        $min = $this->min_subtotal ? ' orders over '.Product::money($this->min_subtotal) : ' every order';

        if ((float) $this->value <= 0 && $this->free_shipping) {
            return 'Free shipping on'.$min;
        }

        $amount = $this->discount_type === 'fixed' ? Product::money($this->value).' off' : $this->percentLabel().' off';

        return $amount.$min.($this->free_shipping ? ' + free shipping' : '');
    }

    private function percentLabel(): string
    {
        return rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.').'%';
    }

    private function spendDiscount(float $subtotal): float
    {
        if ($this->min_subtotal !== null && $subtotal < (float) $this->min_subtotal) {
            return 0.0;
        }

        $discount = $this->discount_type === 'fixed'
            ? (float) $this->value
            : $subtotal * ((float) $this->value / 100);

        return round(min($discount, $subtotal), 2);
    }

    /**
     * @param  Collection<int, array{variant: ProductVariant, qty: int, unit: float, total: float}>  $lines
     */
    private function categoryPercentDiscount(Collection $lines): float
    {
        $eligible = (float) $this->qualifyingLines($lines)->sum('total');

        return round($eligible * ((float) $this->value / 100), 2);
    }

    /**
     * @param  Collection<int, array{variant: ProductVariant, qty: int, unit: float, total: float}>  $lines
     */
    private function bogoDiscount(Collection $lines): float
    {
        $buy = max(1, (int) $this->buy_qty);
        $get = max(1, (int) $this->get_qty);
        $groupSize = $buy + $get;

        // One "unit" per garment, cheapest first — a retailer discounts the
        // least expensive qualifying item(s) in each buy+get group.
        $units = $this->qualifyingLines($lines)
            ->flatMap(fn (array $line) => array_fill(0, $line['qty'], (float) $line['unit']))
            ->sort()
            ->values();

        $freeUnits = intdiv($units->count(), $groupSize) * $get;

        return $freeUnits > 0 ? round((float) $units->take($freeUnits)->sum(), 2) : 0.0;
    }

    /**
     * @param  Collection<int, array{variant: ProductVariant, qty: int, unit: float, total: float}>  $lines
     * @return Collection<int, array{variant: ProductVariant, qty: int, unit: float, total: float}>
     */
    private function qualifyingLines(Collection $lines): Collection
    {
        if ($this->products->isNotEmpty()) {
            $ids = $this->products->pluck('id')->all();

            return $lines->filter(fn (array $line) => in_array($line['variant']->product_id, $ids, true));
        }

        if ($this->category_id === null) {
            return $lines;
        }

        $ids = $this->category?->selfAndDescendantIds() ?? [$this->category_id];

        return $lines->filter(fn (array $line) => in_array($line['variant']->product->category_id, $ids, true));
    }
}
