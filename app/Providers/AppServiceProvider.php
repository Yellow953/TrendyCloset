<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\Order;
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
        $this->composeAdminChrome();

        // partials/seo renders whatever the controller put on the scoped Seo
        // instance; resolving it here keeps every action from passing it along.
        View::composer('partials.seo', fn ($view) => $view->with('seo', $this->app->make(Seo::class)));
    }

    /**
     * The header and footer appear on every storefront page and both want the
     * navigation tree; composing it here spares every controller action from
     * having to remember to pass it. The Catalog is scoped, so the two partials
     * share one set of queries.
     *
     * The counts are the header's alone — the footer renders neither, and
     * composing them for it meant counting the visitor's favourites twice on
     * every page in the shop.
     */
    private function composeChrome(): void
    {
        View::composer(['partials.header', 'partials.footer'], function ($view) {
            $catalog = $this->app->make(Catalog::class);

            $view->with([
                'catalog' => $catalog,
                'navTree' => $catalog->tree(),
            ]);
        });

        View::composer('partials.header', function ($view) {
            $view->with([
                'bagCount' => $this->app->make(Cart::class)->count(),
                'favoritesCount' => $this->favoritesCount(),
            ]);
        });
    }

    /**
     * The back-office sidebar and top bar both want to know how much work is
     * waiting: orders nobody has fulfilled and enquiries nobody has opened.
     * Composed here so no admin action has to remember to pass them.
     */
    private function composeAdminChrome(): void
    {
        View::composer(['partials.admin.sidebar', 'partials.admin.topbar'], function ($view) {
            $view->with([
                'openOrders' => Order::open()->count(),
                'unreadMessageCount' => ContactMessage::whereNull('read_at')->count(),
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
