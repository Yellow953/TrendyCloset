<?php

namespace App\Enums;

/**
 * Append-only product interactions. Favourites are deliberately NOT here —
 * they are toggleable state and live in `product_favorites`.
 */
enum ProductEventType: string
{
    case View = 'view';
    case AddToCart = 'add_to_cart';

    public function label(): string
    {
        return match ($this) {
            self::View => 'Views',
            self::AddToCart => 'Added to bag',
        };
    }
}
