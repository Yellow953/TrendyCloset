<?php

namespace App\Models;

use App\Enums\ProductEventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One recorded product interaction. Append-only: rows are never updated, and
 * the model has no `updated_at`.
 */
class ProductEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'type',
        'visitor_id',
        'customer_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductEventType::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Product, ProductEvent>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Customer, ProductEvent>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @param  Builder<ProductEvent>  $query
     */
    public function scopeOfType(Builder $query, ProductEventType $type): void
    {
        $query->where('type', $type);
    }

    /**
     * @param  Builder<ProductEvent>  $query
     */
    public function scopeSince(Builder $query, \DateTimeInterface $date): void
    {
        $query->where('created_at', '>=', $date);
    }
}
