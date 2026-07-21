<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    /** @use HasFactory<\Database\Factories\CartFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /**
     * @return BelongsTo<User, Cart>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<CartItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Sum of all line totals in the cart.
     */
    public function subtotal(): float
    {
        return (float) $this->items->sum(fn (CartItem $item) => $item->line_total);
    }

    /**
     * Total quantity of items in the cart (the header bag count).
     */
    public function itemCount(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
