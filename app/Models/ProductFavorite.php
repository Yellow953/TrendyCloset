<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A visitor's current favourite. Presence of the row is the state — there is no
 * boolean to keep in sync, and unfavouriting deletes it.
 */
class ProductFavorite extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'visitor_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Product, ProductFavorite>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
