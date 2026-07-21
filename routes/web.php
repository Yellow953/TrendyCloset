<?php

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
Route::get('/favorites', [StoreController::class, 'favorites'])->name('favorites');

// The storefront used to live at /women, before the nav was driven by the DB.
Route::redirect('/women', '/shop');

/*
| Bag — session-backed, no `carts` table. Checkout renders the real bag but
| does not yet place an order.
*/
Route::get('/bag', [CartController::class, 'index'])->name('cart');
Route::post('/bag', [CartController::class, 'store'])->name('cart.add');
Route::patch('/bag/{variant}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/bag/{variant}', [CartController::class, 'destroy'])->name('cart.remove');
Route::post('/bag/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
Route::delete('/bag/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');

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
