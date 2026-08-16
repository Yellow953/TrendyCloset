<?php

namespace App\Support;

/**
 * One thing a shopper did, described once and in neutral terms.
 *
 * Meta and GA4 want the same facts under different names and in different
 * shapes, so nothing here is spelled the way either vendor spells it —
 * {@see Tracking} translates. Adding a third destination means adding one
 * translator, not touching a single controller.
 */
final class TrackedEvent
{
    /**
     * @param  string  $name  canonical name — see Tracking::META / Tracking::GA4
     * @param  array<int, array{id: string, name: ?string, category: ?string, price: float, quantity: int}>  $items
     * @param  array<string, mixed>  $extra  event-specific fields (search term, order number)
     */
    public function __construct(
        public readonly string $name,
        public readonly array $items = [],
        public readonly ?float $value = null,
        public readonly array $extra = [],
    ) {}

    /**
     * Distinct product ids, in the order they were added.
     *
     * @return array<int, string>
     */
    public function ids(): array
    {
        return array_values(array_unique(array_column($this->items, 'id')));
    }

    public function quantity(): int
    {
        return (int) array_sum(array_column($this->items, 'quantity'));
    }
}
