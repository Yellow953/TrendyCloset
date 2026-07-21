<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\ProductFavorite;
use App\Support\Cart;
use App\Support\Catalog;
use App\Support\Visitor;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Both are per-request state: one bag, one set of catalogue queries.
        $this->app->scoped(Cart::class, fn ($app) => new Cart($app->make(Session::class)));
        $this->app->scoped(Catalog::class, fn () => new Catalog);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->composeChrome();
    }

    /**
     * The header and footer appear on every storefront page and always need the
     * same things: the navigation tree, the bag totals and the visitor's
     * favourites count. Composing them here spares every controller action from
     * having to remember to pass them.
     */
    private function composeChrome(): void
    {
        View::composer(['partials.header', 'partials.footer'], function ($view) {
            $cart = $this->app->make(Cart::class);
            $catalog = $this->app->make(Catalog::class);

            $view->with([
                'catalog' => $catalog,
                'navTree' => $catalog->tree(),
                'bagCount' => $cart->count(),
                'bagTotal' => Product::money($cart->subtotal()),
                'favoritesCount' => $this->favoritesCount(),
            ]);
        });
    }

    /**
     * Zero outside a web request, where TrackVisitor has not bound an identity.
     */
    private function favoritesCount(): int
    {
        if (! $this->app->bound(Visitor::class)) {
            return 0;
        }

        return ProductFavorite::where('visitor_id', $this->app->make(Visitor::class)->id)->count();
    }
}
