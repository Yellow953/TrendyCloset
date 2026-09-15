<?php

namespace App\Support;

use App\Enums\OfferType;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * The currently-live offers, read once per request and reused everywhere they
 * are advertised — product cards, the PDP, the cart — so a page with twenty
 * cards costs one query rather than twenty.
 */
class OfferBoard
{
    /** @var Collection<int, Offer>|null */
    private ?Collection $offers = null;

    /**
     * @return Collection<int, Offer>
     */
    public function live(): Collection
    {
        return $this->offers ??= Offer::live()->with(['category', 'products'])->latest('id')->get();
    }

    /**
     * Product-facing offers (category %, BOGO) that cover the given product —
     * what a card or the PDP badges.
     *
     * @return Collection<int, Offer>
     */
    public function forProduct(Product $product): Collection
    {
        return $this->live()->filter(fn (Offer $offer) => $offer->coversProduct($product))->values();
    }

    /**
     * Whether any live offer grants free shipping once the given subtotal is
     * reached, independent of which discount (if any) actually applies.
     */
    public function grantsFreeShipping(float $subtotal): bool
    {
        return $this->live()
            ->filter(fn (Offer $offer) => $offer->type === OfferType::Spend && $offer->free_shipping)
            ->contains(fn (Offer $offer) => $offer->min_subtotal === null || $subtotal >= (float) $offer->min_subtotal);
    }
}
