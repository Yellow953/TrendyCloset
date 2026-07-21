<?php

namespace App\Models;

use App\Enums\ProductEventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'compare_at_price',
        'badge',
        'rating',
        'is_featured',
        'is_active',
        'sale_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sale_ends_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Category, Product>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductImage>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * @return HasMany<ProductVariant>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * @return HasMany<ProductEvent>
     */
    public function events(): HasMany
    {
        return $this->hasMany(ProductEvent::class);
    }

    /**
     * @return HasMany<ProductFavorite>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(ProductFavorite::class);
    }

    /**
     * The primary image, falling back to the first image by position.
     */
    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true)
            ?? $this->images->sortBy('position')->first();
    }

    /**
     * The URL of the primary image — what every product card renders.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->primaryImage()?->url;
    }

    /**
     * Display price, e.g. "$54.00".
     */
    public function getPriceLabelAttribute(): string
    {
        return self::money($this->price);
    }

    /**
     * Strike-through "was" price, or null when the product is not discounted.
     */
    public function getCompareLabelAttribute(): ?string
    {
        return $this->on_sale ? self::money($this->compare_at_price) : null;
    }

    /**
     * The corner badge: whatever merchandising set, else a derived "-20%".
     */
    public function getBadgeLabelAttribute(): ?string
    {
        return $this->badge ?: ($this->on_sale ? '-'.$this->discount_percent.'%' : null);
    }

    /**
     * The variant a quick "add to bag" from a product card should use: the
     * first active size with stock. Null when the piece is sold out.
     */
    public function getDefaultVariantAttribute(): ?ProductVariant
    {
        return $this->variants->first(fn (ProductVariant $v) => $v->is_active && $v->stock > 0);
    }

    /**
     * Whether any active variant has stock left.
     */
    public function getInStockAttribute(): bool
    {
        return $this->variants->where('is_active', true)->sum('stock') > 0;
    }

    public static function money(int|float|string|null $amount): string
    {
        return '$'.number_format((float) $amount, 2);
    }

    /**
     * Whether the product is discounted (has a strike-through "was" price).
     */
    public function getOnSaleAttribute(): bool
    {
        return $this->compare_at_price !== null
            && (float) $this->compare_at_price > (float) $this->price;
    }

    /**
     * Percentage off, derived from compare_at_price vs price (0 when not on sale).
     */
    public function getDiscountPercentAttribute(): int
    {
        if (! $this->on_sale) {
            return 0;
        }

        return (int) round(
            (((float) $this->compare_at_price - (float) $this->price) / (float) $this->compare_at_price) * 100
        );
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Eager-load engagement counts for the CRM dashboard, optionally windowed
     * to events since a given date. Adds `views_count`, `add_to_cart_count`
     * and `favorites_count` — favourites are never windowed, since the row
     * represents current state rather than something that happened.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeWithEngagement(Builder $query, ?\DateTimeInterface $since = null): void
    {
        $window = fn (Builder $q) => $since
            ? $q->where('product_events.created_at', '>=', $since)
            : $q;

        $query->withCount([
            'events as views_count' => fn (Builder $q) => $window(
                $q->where('type', ProductEventType::View)
            ),
            'events as add_to_cart_count' => fn (Builder $q) => $window(
                $q->where('type', ProductEventType::AddToCart)
            ),
            'favorites as favorites_count',
        ]);
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopeOnDeal(Builder $query): void
    {
        $query->whereNotNull('sale_ends_at')->where('sale_ends_at', '>', now());
    }

    /**
     * Discounted products — the "Sale" edit.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeOnSale(Builder $query): void
    {
        $query->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'price');
    }

    /**
     * The "New in" edit: anything merchandised as NEW, newest first.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeNewArrivals(Builder $query): void
    {
        $query->where('badge', 'NEW');
    }

    /**
     * Constrain to a category *and everything beneath it*, so browsing a parent
     * like "Winter Section" returns the products filed under its subcategories.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeInCategory(Builder $query, Category $category): void
    {
        $query->whereIn('category_id', $category->selfAndDescendantIds());
    }
}
