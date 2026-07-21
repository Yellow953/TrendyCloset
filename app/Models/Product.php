<?php

namespace App\Models;

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
     * The primary image, falling back to the first image by position.
     */
    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true)
            ?? $this->images->sortBy('position')->first();
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
     * @param  Builder<Product>  $query
     */
    public function scopeOnDeal(Builder $query): void
    {
        $query->whereNotNull('sale_ends_at')->where('sale_ends_at', '>', now());
    }
}
