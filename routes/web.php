<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');

/*
| Crawler surfaces. robots.txt is a route rather than a file in public/ so it
| can name the sitemap at whatever domain APP_URL points at; llms.txt is the
| plain-language brief for LLM agents.
*/
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');

/*
| Catalogue. One listing action serves the whole shop, a single category and
| the New in / Sale edits (`?edit=`), so filters and sorting behave identically
| whichever door the shopper came through.
*/
Route::get('/shop/{category:slug?}', [StoreController::class, 'listing'])->name('listing');
Route::get('/product/{product:slug}', [StoreController::class, 'product'])->name('product');
Route::post('/product/{product:slug}/favorite', [StoreController::class, 'favorite'])->name('product.favorite');
Route::get('/favorites/drawer', [StoreController::class, 'favoritesDrawer'])->name('favorites.drawer');
Route::get('/favorites', [StoreController::class, 'favorites'])->name('favorites');

// The storefront used to live at /women, before the nav was driven by the DB.
Route::redirect('/women', '/shop');

/*
| Bag — session-backed, no `carts` table. Checkout renders the real bag but
| does not yet place an order.
*/
Route::get('/bag', [CartController::class, 'index'])->name('cart');
Route::get('/bag/drawer', [CartController::class, 'drawer'])->name('cart.drawer');
Route::post('/bag', [CartController::class, 'store'])->name('cart.add');
Route::patch('/bag/{variant}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/bag/{variant}', [CartController::class, 'destroy'])->name('cart.remove');
Route::post('/bag/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
Route::delete('/bag/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [CartController::class, 'placeOrder'])->name('checkout.place');
// Gated on the session that placed it, not on the number in the URL.
Route::get('/order/{number}', [CartController::class, 'confirmed'])->name('order.confirmed');

Route::get('/about', [StoreController::class, 'about'])->name('about');
Route::get('/contact', [StoreController::class, 'contact'])->name('contact');
Route::post('/contact', [StoreController::class, 'sendContact'])->name('contact.send');
// Five policy documents behind one action; /policies opens the first.
Route::get('/policies/{topic?}', [StoreController::class, 'policies'])->name('policies');

/*
| Admin authentication — the storefront is public; these routes gate the
| back-office CRM. Registration is intentionally disabled: staff accounts are
| created via seeder/tinker, never self-service.
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);
});

/*
| Back office. Every authenticated user is staff (customers cannot sign in), so
| `auth` gates the CRM as a whole; the narrower `admin` group below covers the
| things only an administrator should touch — discount codes and staff accounts.
*/
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('products/{product}/toggle', [AdminProductController::class, 'toggle'])->name('products.toggle');

    Route::patch('products/{product}/images/{image}/primary', [ProductImageController::class, 'primary'])->name('products.images.primary');
    Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
    Route::patch('products/{product}/images/reorder', [ProductImageController::class, 'reorder'])->name('products.images.reorder');

    Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
    Route::post('categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::get('categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('orders/{order}/notes', [OrderController::class, 'updateNotes'])->name('orders.notes');

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::patch('messages/{message}/unread', [MessageController::class, 'unread'])->name('messages.unread');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    // Admin-only: discount codes and who may sign in.
    Route::middleware('admin')->group(function () {
        // Both are short forms, so they are created and edited in modals on
        // their own index rather than on a page of their own.
        Route::get('coupons', [CouponController::class, 'index'])->name('coupons.index');
        Route::post('coupons', [CouponController::class, 'store'])->name('coupons.store');
        Route::put('coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
        Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
