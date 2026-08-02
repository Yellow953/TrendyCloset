<?php

namespace App\Models;

use App\Enums\SiteEventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One thing that happened on the shop. Append-only: there is no `updated_at`
 * and nothing should ever update a row.
 */
class SiteEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'visitor_id',
        'session_id',
        'path',
        'referrer_host',
        'referrer',
        'product_id',
        'order_id',
        'meta',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'name' => SiteEventType::class,
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Product, SiteEvent>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Order, SiteEvent>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @param  Builder<SiteEvent>  $query
     */
    public function scopeOfType(Builder $query, SiteEventType $type): void
    {
        $query->where('name', $type);
    }

    /**
     * @param  Builder<SiteEvent>  $query
     */
    public function scopePageViews(Builder $query): void
    {
        $query->where('name', SiteEventType::PageView);
    }
}
