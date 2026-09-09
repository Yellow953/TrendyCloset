<?php

namespace App\Enums;

/**
 * Shapes an automatic, cart-level {@see \App\Models\Offer} can take — distinct
 * from a Coupon, which requires the shopper to type a code.
 */
enum OfferType: string
{
    case Spend = 'spend';
    case CategoryPercent = 'category_percent';
    case Bogo = 'bogo';

    public function label(): string
    {
        return match ($this) {
            self::Spend => 'Spend threshold',
            self::CategoryPercent => 'Category % off',
            self::Bogo => 'Buy X get Y free',
        };
    }
}
