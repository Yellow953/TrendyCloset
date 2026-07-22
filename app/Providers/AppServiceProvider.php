<?php

namespace App\Providers;

use App\Models\ProductFavorite;
use App\Support\Cart;
use App\Support\Catalog;
use App\Support\Seo;
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
        // All three are per-request state: one bag, one set of catalogue
        // queries, one page's metadata.
        $this->app->scoped(Cart::class, fn ($app) => new Cart($app->make(Session::class)));
        $this->app->scoped(Catalog::class, fn () => new Catalog);
        $this->app->scoped(Seo::class, fn () => new Seo);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->composeChrome();

        // partials/seo renders whatever the controller put on the scoped Seo
        // instance; resolving it here keeps every action from passing it along.
        View::composer('partials.seo', fn ($view) => $view->with('seo', $this->app->make(Seo::class)));
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
