<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/women', [StoreController::class, 'listing'])->name('listing');
Route::get('/product', [StoreController::class, 'product'])->name('product');
Route::get('/bag', [StoreController::class, 'cart'])->name('cart');
Route::get('/checkout', [StoreController::class, 'checkout'])->name('checkout');
Route::get('/about', [StoreController::class, 'about'])->name('about');
Route::get('/contact', [StoreController::class, 'contact'])->name('contact');
Route::get('/policies', [StoreController::class, 'policies'])->name('policies');

Auth::routes();
