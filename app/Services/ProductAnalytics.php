<?php

namespace App\Services;

use App\Enums\ProductEventType;
use App\Models\Product;
use App\Models\ProductEvent;
use App\Models\ProductFavorite;
use App\Support\Visitor;
use Illuminate\Support\Facades\Cache;

/**
 * Records product interactions. The write side of the analytics tables — read
 * them back with plain aggregate queries off ProductEvent / ProductFavorite.
 */
class ProductAnalytics
{
    /**
     * How long a repeat view by the same visitor is ignored. Without this, a
     * refresh or a crawler inflates the "most viewed" list into nonsense.
     */
    private const VIEW_DEDUPE = 1800;

    public function __construct(private readonly Visitor $visitor) {}

    /**
     * Record a product view, at most once per visitor per product per window.
     * Returns false when the view was deduplicated.
     */
    public function recordView(Product $product): bool
    {
        $key = "view:{$this->visitor->id}:{$product->id}";

        if (! Cache::add($key, true, self::VIEW_DEDUPE)) {
            return false;
        }

        $this->record($product, ProductEventType::View);

        return true;
    }

    /**
     * Record an add-to-bag. Not deduplicated — adding the same item twice is a
     * real signal, unlike refreshing a page.
     */
    public function recordAddToCart(Product $product): void
    {
        $this->record($product, ProductEventType::AddToCart);
    }

    /**
     * Toggle the visitor's favourite. Returns the new state: true if the
     * product is now favourited.
     */
    public function toggleFavorite(Product $product): bool
    {
        $existing = ProductFavorite::where('product_id', $product->id)
            ->where('visitor_id', $this->visitor->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        ProductFavorite::create([
            'product_id' => $product->id,
            'visitor_id' => $this->visitor->id,
        ]);

        return true;
    }

    /**
     * Whether the current visitor has favourited the product.
     */
    public function hasFavorited(Product $product): bool
    {
        return ProductFavorite::where('product_id', $product->id)
            ->where('visitor_id', $this->visitor->id)
            ->exists();
    }

    private function record(Product $product, ProductEventType $type): void
    {
        ProductEvent::create([
            'product_id' => $product->id,
            'type' => $type,
            'visitor_id' => $this->visitor->id,
        ]);
    }
}
