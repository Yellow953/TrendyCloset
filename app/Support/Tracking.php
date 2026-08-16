<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * The storefront's marketing analytics: one description of what a shopper did,
 * rendered for both the Meta pixel and GA4.
 *
 * Shaped like {@see Seo}. Controllers say what happened in the shop's own terms
 * (`$this->tracking->productViewed($product)`) and `partials/tracking.blade.php`
 * renders whatever each destination needs. Views never call `fbq()` or `gtag()`,
 * and no controller knows a vendor's event names — that mapping lives in the
 * two constants below, which is the only place it should ever be edited.
 *
 * Events that happen *after* the page renders — add to bag, favourite — cannot
 * be queued. Those controllers return {@see self::payload()} in their JSON and
 * app.js fires it, so the numbers still come from server-side prices rather
 * than being scraped out of the DOM.
 *
 * Either destination renders nothing when its id is unset, so local and staging
 * cost a visitor nothing and never pollute a live property.
 */
class Tracking
{
    /** Canonical name => Meta standard event. */
    private const META = [
        'product_viewed' => 'ViewContent',
        'searched' => 'Search',
        'added_to_cart' => 'AddToCart',
        'saved' => 'AddToWishlist',
        'checkout_started' => 'InitiateCheckout',
        'purchased' => 'Purchase',
    ];

    /** Canonical name => GA4 recommended event. */
    private const GA4 = [
        'product_viewed' => 'view_item',
        'searched' => 'search',
        'added_to_cart' => 'add_to_cart',
        'saved' => 'add_to_wishlist',
        'checkout_started' => 'begin_checkout',
        'purchased' => 'purchase',
    ];

    /** @var array<int, TrackedEvent> */
    private array $events = [];

    public static function metaPixelId(): ?string
    {
        return self::clean(config('services.meta.pixel_id'));
    }

    public static function ga4Id(): ?string
    {
        return self::clean(config('services.google.ga4_id'));
    }

    public static function enabled(): bool
    {
        return self::metaPixelId() !== null || self::ga4Id() !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | What happened — the vocabulary controllers use
    |--------------------------------------------------------------------------
    */

    public function productViewed(Product $product): static
    {
        return $this->push(new TrackedEvent(
            name: 'product_viewed',
            items: [self::item($product)],
            value: self::amount($product->price),
        ));
    }

    public function searched(string $term, int $results): static
    {
        return $this->push(new TrackedEvent(
            name: 'searched',
            extra: ['term' => mb_substr($term, 0, 120), 'results' => $results],
        ));
    }

    /**
     * The bag, as {@see Cart::lines()} returns it.
     *
     * @param  Collection<int, array<string, mixed>>  $lines
     */
    public function checkoutStarted(Collection $lines, float $value): static
    {
        return $this->push(new TrackedEvent(
            name: 'checkout_started',
            items: $lines->map(fn (array $line) => self::item(
                $line['variant']->product,
                (int) $line['qty'],
                (float) $line['unit'],
            ))->values()->all(),
            value: self::amount($value),
        ));
    }

    /**
     * The conversion. Read off the placed order, not the bag — the bag is empty
     * by the time the confirmation page renders.
     *
     * `items.variant` is nullable (a deleted variant nulls the column), so those
     * lines fall back to the SKU snapshotted onto the order item.
     */
    public function purchased(Order $order): static
    {
        return $this->push(new TrackedEvent(
            name: 'purchased',
            items: $order->items->map(fn ($item) => [
                'id' => $item->variant?->product_id
                    ? self::contentId($item->variant->product_id)
                    : ($item->sku ?: 'TC-unknown'),
                'name' => $item->product_name,
                'category' => null,
                'price' => self::amount($item->unit_price),
                'quantity' => (int) $item->quantity,
            ])->values()->all(),
            value: self::amount($order->grand_total),
            extra: [
                'order_number' => $order->order_number,
                'shipping' => self::amount($order->shipping_total ?? 0),
            ],
        ));
    }

    private function push(TrackedEvent $event): static
    {
        $this->events[] = $event;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Rendering — read by partials/tracking.blade.php
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<int, array{name: string, params: array<string, mixed>}>
     */
    public function metaEvents(): array
    {
        return array_map(fn (TrackedEvent $e) => self::toMeta($e), $this->events);
    }

    /**
     * @return array<int, array{name: string, params: array<string, mixed>}>
     */
    public function ga4Events(): array
    {
        return array_map(fn (TrackedEvent $e) => self::toGa4($e), $this->events);
    }

    /*
    |--------------------------------------------------------------------------
    | Payloads for events fired from JavaScript
    |--------------------------------------------------------------------------
    */

    /**
     * The `tracking` key a JSON response carries so app.js can report something
     * that happened without a page load. Null when nothing is configured, which
     * is what lets the client-side guard be a plain truthiness check.
     *
     * @return array{meta: array<string, mixed>, ga4: array<string, mixed>}|null
     */
    public static function payload(TrackedEvent $event): ?array
    {
        if (! self::enabled()) {
            return null;
        }

        return [
            'meta' => self::toMeta($event),
            'ga4' => self::toGa4($event),
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, ga4: array<string, mixed>}|null
     */
    public static function addedToCart(Product $product, int $quantity, float $lineTotal): ?array
    {
        return self::payload(new TrackedEvent(
            name: 'added_to_cart',
            items: [self::item($product, $quantity)],
            value: self::amount($lineTotal),
        ));
    }

    /**
     * @return array{meta: array<string, mixed>, ga4: array<string, mixed>}|null
     */
    public static function saved(Product $product): ?array
    {
        return self::payload(new TrackedEvent(
            name: 'saved',
            items: [self::item($product)],
            value: self::amount($product->price),
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Translation
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{name: string, params: array<string, mixed>}
     */
    private static function toMeta(TrackedEvent $event): array
    {
        $params = [
            'content_type' => 'product',
            'currency' => config('seo.currency'),
            'value' => $event->value,
        ];

        if ($event->items !== []) {
            $params['content_ids'] = $event->ids();
            $params['contents'] = array_map(fn (array $i) => [
                'id' => $i['id'],
                'quantity' => $i['quantity'],
                'item_price' => $i['price'],
            ], $event->items);
            $params['num_items'] = $event->quantity();
        }

        // A single-product event reads better in Events Manager with its name on it.
        if (count($event->items) === 1) {
            $params['content_name'] = $event->items[0]['name'];
            $params['content_category'] = $event->items[0]['category'];
        }

        if ($event->name === 'searched') {
            $params['search_string'] = $event->extra['term'];
            $params['num_items'] = $event->extra['results'];
            // A search has no money attached to it; a currency with no value
            // beside it just reads as a misconfigured event.
            unset($params['currency']);
        }

        if ($event->name === 'purchased') {
            $params['order_id'] = $event->extra['order_number'];
        }

        return ['name' => self::META[$event->name], 'params' => self::prune($params)];
    }

    /**
     * @return array{name: string, params: array<string, mixed>}
     */
    private static function toGa4(TrackedEvent $event): array
    {
        $params = [
            'currency' => config('seo.currency'),
            'value' => $event->value,
        ];

        if ($event->items !== []) {
            $params['items'] = array_map(fn (array $i) => self::prune([
                'item_id' => $i['id'],
                'item_name' => $i['name'],
                'item_category' => $i['category'],
                'price' => $i['price'],
                'quantity' => $i['quantity'],
            ]), $event->items);
        }

        if ($event->name === 'searched') {
            $params['search_term'] = $event->extra['term'];
            // GA4 has no currency/value on a search; sending them muddies reports.
            unset($params['currency'], $params['value']);
        }

        if ($event->name === 'purchased') {
            $params['transaction_id'] = $event->extra['order_number'];
            $params['shipping'] = $event->extra['shipping'];
        }

        return ['name' => self::GA4[$event->name], 'params' => self::prune($params)];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private static function prune(array $params): array
    {
        return array_filter($params, fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    /**
     * @return array{id: string, name: ?string, category: ?string, price: float, quantity: int}
     */
    private static function item(Product $product, int $quantity = 1, ?float $price = null): array
    {
        return [
            'id' => self::contentId($product),
            'name' => $product->name,
            'category' => $product->category?->name,
            'price' => self::amount($price ?? $product->price),
            'quantity' => $quantity,
        ];
    }

    /**
     * The catalogue id for a product. Must stay identical to the `sku` in
     * {@see Schema::product()} and the `product:retailer_item_id` OG tag — it is
     * the join key between the structured data, both analytics destinations and
     * any future catalogue feed.
     */
    public static function contentId(Product|int $product): string
    {
        return 'TC-'.($product instanceof Product ? $product->id : $product);
    }

    private static function amount(float|string|null $value): float
    {
        return round((float) $value, 2);
    }

    private static function clean(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
