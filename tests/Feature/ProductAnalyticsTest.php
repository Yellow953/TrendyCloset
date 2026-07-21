<?php

use App\Enums\ProductEventType;
use App\Models\Product;
use App\Models\ProductEvent;
use App\Models\ProductFavorite;
use App\Services\ProductAnalytics;
use App\Support\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function analytics(string $visitorId = 'visitor-a'): ProductAnalytics
{
    return new ProductAnalytics(new Visitor($visitorId));
}

it('issues a visitor cookie and keeps it stable across requests', function () {
    $first = $this->get('/');
    $id = $first->getCookie(Visitor::COOKIE)->getValue();

    expect($id)->not->toBeEmpty();

    // withCookie() encrypts for us, mirroring what the browser sends back.
    $second = $this->withCookie(Visitor::COOKIE, $id)->get('/women');

    expect($second->getCookie(Visitor::COOKIE)->getValue())->toBe($id);
});

it('records a view once per visitor within the dedupe window', function () {
    $product = Product::factory()->create();

    expect(analytics()->recordView($product))->toBeTrue()
        ->and(analytics()->recordView($product))->toBeFalse()
        ->and(analytics('visitor-b')->recordView($product))->toBeTrue();

    expect(ProductEvent::where('type', ProductEventType::View)->count())->toBe(2);
});

it('does not dedupe add to cart, since repeat adds are real signal', function () {
    $product = Product::factory()->create();

    analytics()->recordAddToCart($product);
    analytics()->recordAddToCart($product);

    expect(ProductEvent::where('type', ProductEventType::AddToCart)->count())->toBe(2);
});

it('toggles a favourite on and off', function () {
    $product = Product::factory()->create();
    $a = analytics();

    expect($a->toggleFavorite($product))->toBeTrue()
        ->and($a->hasFavorited($product))->toBeTrue()
        ->and($a->toggleFavorite($product))->toBeFalse()
        ->and($a->hasFavorited($product))->toBeFalse()
        ->and(ProductFavorite::count())->toBe(0);
});

it('keeps favourites separate per visitor', function () {
    $product = Product::factory()->create();

    analytics('visitor-a')->toggleFavorite($product);
    analytics('visitor-b')->toggleFavorite($product);

    expect(ProductFavorite::count())->toBe(2)
        ->and(analytics('visitor-c')->hasFavorited($product))->toBeFalse();
});

it('aggregates engagement counts, windowing events but not favourites', function () {
    $product = Product::factory()->create();

    analytics('visitor-a')->recordView($product);
    analytics('visitor-b')->recordView($product);
    analytics('visitor-a')->recordAddToCart($product);
    analytics('visitor-a')->toggleFavorite($product);

    $all = Product::withEngagement()->find($product->id);

    expect($all->views_count)->toBe(2)
        ->and($all->add_to_cart_count)->toBe(1)
        ->and($all->favorites_count)->toBe(1);

    // Favourites are current state, so a future window must not zero them out.
    $future = Product::withEngagement(now()->addDay())->find($product->id);

    expect($future->views_count)->toBe(0)
        ->and($future->add_to_cart_count)->toBe(0)
        ->and($future->favorites_count)->toBe(1);
});

it('drops analytics rows when the product is deleted', function () {
    $product = Product::factory()->create();

    analytics()->recordView($product);
    analytics()->toggleFavorite($product);

    $product->delete();

    expect(ProductEvent::count())->toBe(0)
        ->and(ProductFavorite::count())->toBe(0);
});
