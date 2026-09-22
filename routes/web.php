<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\OfferController;
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

// SEO
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');
Route::get('/feed/meta-products.xml', [SeoController::class, 'metaFeed'])->name('feed.meta');

// Shop
Route::get('/shop/{category:slug?}', [StoreController::class, 'listing'])->name('listing');
Route::get('/product/{product:slug}', [StoreController::class, 'product'])->name('product');
Route::post('/product/{product:slug}/favorite', [StoreController::class, 'favorite'])->name('product.favorite');
Route::get('/favorites/drawer', [StoreController::class, 'favoritesDrawer'])->name('favorites.drawer');
Route::get('/favorites', [StoreController::class, 'favorites'])->name('favorites');
Route::redirect('/women', '/shop');
Route::get('/bag', [CartController::class, 'index'])->name('cart');
Route::get('/bag/drawer', [CartController::class, 'drawer'])->name('cart.drawer');
Route::post('/bag', [CartController::class, 'store'])->name('cart.add');
Route::patch('/bag/{variant}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/bag/{variant}', [CartController::class, 'destroy'])->name('cart.remove');
Route::post('/bag/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
Route::delete('/bag/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [CartController::class, 'placeOrder'])->name('checkout.place');
Route::get('/order/{number}', [CartController::class, 'confirmed'])->name('order.confirmed');
Route::get('/about', [StoreController::class, 'about'])->name('about');
Route::get('/contact', [StoreController::class, 'contact'])->name('contact');
Route::post('/contact', [StoreController::class, 'sendContact'])->name('contact.send');
Route::get('/policies/{topic?}', [StoreController::class, 'policies'])->name('policies');
Route::post('/track/whatsapp', [StoreController::class, 'trackWhatsapp'])->middleware('throttle:30,1')->name('track.whatsapp');

// Auth
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

// Back Office
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

    Route::get('colors', [ColorController::class, 'index'])->name('colors.index');
    Route::get('colors/create', [ColorController::class, 'create'])->name('colors.create');
    Route::post('colors', [ColorController::class, 'store'])->name('colors.store');
    Route::get('colors/{color}/edit', [ColorController::class, 'edit'])->name('colors.edit');
    Route::put('colors/{color}', [ColorController::class, 'update'])->name('colors.update');
    Route::delete('colors/{color}', [ColorController::class, 'destroy'])->name('colors.destroy');

    Route::get('slides', [HeroSlideController::class, 'index'])->name('slides.index');
    Route::get('slides/create', [HeroSlideController::class, 'create'])->name('slides.create');
    Route::post('slides', [HeroSlideController::class, 'store'])->name('slides.store');
    Route::get('slides/{slide}/edit', [HeroSlideController::class, 'edit'])->name('slides.edit');
    Route::put('slides/{slide}', [HeroSlideController::class, 'update'])->name('slides.update');
    Route::patch('slides/{slide}/toggle', [HeroSlideController::class, 'toggle'])->name('slides.toggle');
    Route::delete('slides/{slide}', [HeroSlideController::class, 'destroy'])->name('slides.destroy');

    Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::patch('announcements/{announcement}/toggle', [AnnouncementController::class, 'toggle'])->name('announcements.toggle');
    Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

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

    Route::middleware('admin')->group(function () {
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');
        Route::get('coupons', [CouponController::class, 'index'])->name('coupons.index');
        Route::post('coupons', [CouponController::class, 'store'])->name('coupons.store');
        Route::put('coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
        Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');

        Route::get('offers', [OfferController::class, 'index'])->name('offers.index');
        Route::post('offers', [OfferController::class, 'store'])->name('offers.store');
        Route::put('offers/{offer}', [OfferController::class, 'update'])->name('offers.update');
        Route::delete('offers/{offer}', [OfferController::class, 'destroy'])->name('offers.destroy');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
