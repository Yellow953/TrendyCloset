<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'color',
        'price_override',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Product, ProductVariant>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Price to charge for this variant — its override, else the product price.
     */
    public function getEffectivePriceAttribute(): string
    {
        return $this->price_override ?? $this->product->price;
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock > 0;
    }
}
