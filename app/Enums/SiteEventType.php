<?php

namespace App\Enums;

/**
 * Things that happen on the shop rather than to a garment. Product views and
 * add-to-bag stay in {@see ProductEventType} — they belong to a product and are
 * reported per product; these belong to a visit.
 *
 * The value is what lands in `site_events.name`, so renaming a case rewrites
 * history. Add cases; do not rename them.
 */
enum SiteEventType: string
{
    case PageView = 'page_view';
    case Search = 'search';
    case CheckoutStarted = 'checkout_started';
    case OrderPlaced = 'order_placed';
    case CouponApplied = 'coupon_applied';
    case WhatsappClick = 'whatsapp_click';
    case ContactFormSent = 'contact_form_sent';
    case FavouriteAdded = 'favourite_added';

    public function label(): string
    {
        return match ($this) {
            self::PageView => 'Page views',
            self::Search => 'Searches',
            self::CheckoutStarted => 'Checkouts started',
            self::OrderPlaced => 'Orders placed',
            self::CouponApplied => 'Discount codes applied',
            self::WhatsappClick => 'WhatsApp taps',
            self::ContactFormSent => 'Contact messages',
            self::FavouriteAdded => 'Pieces hearted',
        };
    }

    /**
     * One line saying what the figure actually counts, for the report — the
     * difference between "someone opened checkout" and "someone paid" is the
     * whole point of having both.
     */
    public function hint(): string
    {
        return match ($this) {
            self::PageView => 'Every storefront page opened',
            self::Search => 'Searches run from the header',
            self::CheckoutStarted => 'Reached the checkout form',
            self::OrderPlaced => 'Completed an order',
            self::CouponApplied => 'A code accepted in the bag',
            self::WhatsappClick => 'Tapped through to WhatsApp',
            self::ContactFormSent => 'Sent the contact form',
            self::FavouriteAdded => 'Added a piece to favourites',
        };
    }

    /**
     * The named events, i.e. everything a page view is not. These are what the
     * "custom events" card lists.
     *
     * @return array<int, self>
     */
    public static function custom(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $c) => $c !== self::PageView,
        ));
    }
}
