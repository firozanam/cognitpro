<?php

use App\Modules\Prompt\Http\Controllers\CartController;
use App\Modules\Prompt\Http\Controllers\PromptController;
use App\Modules\Prompt\Http\Controllers\PurchaseController;
use App\Modules\Prompt\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// Public routes - Marketplace (web group: sessions, Inertia shared props,
// asset versioning, route-model binding — RTE-03)
Route::middleware('web')->group(function () {
    Route::get('marketplace', [PromptController::class, 'index'])->name('marketplace.index');
    Route::get('marketplace/search', [PromptController::class, 'search'])->name('marketplace.search');
    Route::get('marketplace/featured', [PromptController::class, 'featured'])->name('marketplace.featured');

    // Public review routes
    Route::get('prompts/{prompt}/reviews', [ReviewController::class, 'index'])->name('prompts.reviews.index');

    // Guest-capable cart routes: guests keep their cart in the session and
    // are asked to log in only at checkout. Checkout and the count endpoint
    // stay authenticated.
    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('cart/add/{prompt}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('cart/{cartId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('cart', [CartController::class, 'clear'])->name('cart.clear');
});

// Authenticated routes
Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // Prompt CRUD (sellers) - must be before {slug} route
    Route::get('prompts/create', [PromptController::class, 'create'])->name('prompts.create');
    Route::post('prompts', [PromptController::class, 'store'])->name('prompts.store');
    Route::get('prompts/{prompt}/edit', [PromptController::class, 'edit'])->name('prompts.edit');
    Route::put('prompts/{prompt}', [PromptController::class, 'update'])->name('prompts.update');
    Route::delete('prompts/{prompt}', [PromptController::class, 'destroy'])->name('prompts.destroy');
    Route::post('prompts/{prompt}/submit', [PromptController::class, 'submitForApproval'])->name('prompts.submit');

    // Cart checkout and count stay authenticated
    Route::post('cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('cart/count', [CartController::class, 'count'])->name('cart.count');

    // Purchase routes (buyers)
    Route::get('checkout/{prompt}', [PurchaseController::class, 'checkout'])->name('purchases.checkout');
    Route::post('purchase/{prompt}', [PurchaseController::class, 'purchase'])->name('purchases.purchase');
    Route::get('purchases/{purchase}/payment', [PurchaseController::class, 'payment'])->name('purchases.payment');
    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

    // Seller routes
    Route::get('seller/sales', [PurchaseController::class, 'sales'])->name('seller.sales');
    Route::get('seller/dashboard', [PurchaseController::class, 'sellerDashboard'])->name('seller.dashboard');

    // Review routes
    Route::get('purchases/{purchase}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('purchases/{purchase}/review', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::get('reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('reviews/{review}/respond', [ReviewController::class, 'respond'])->name('reviews.respond');
    Route::post('reviews/{review}/helpful', [ReviewController::class, 'helpful'])->name('reviews.helpful');
    Route::get('my-reviews', [ReviewController::class, 'myReviews'])->name('reviews.my');
});

// Public prompt show route - must be after authenticated routes to avoid conflicts
Route::middleware('web')->group(function () {
    Route::get('prompts/{slug}', [PromptController::class, 'show'])->name('prompts.show');
});
