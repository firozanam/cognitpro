<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PromptController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
|
| These routes are accessible without authentication for public data
|
*/

// Public categories - no auth required
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Public tags - no auth required
Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/{tag}', [TagController::class, 'show']);

// Public prompts - limited data without auth
Route::get('/prompts', [PromptController::class, 'index']);
Route::get('/prompts/{prompt}', [PromptController::class, 'show']);

// Public reviews - no auth required to view
Route::get('/reviews', [ReviewController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
|
| These routes require authentication via Laravel Sanctum
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Admin Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin'])->group(function () {
        // Admin category management
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
        
        // Admin tag management
        Route::post('/tags', [TagController::class, 'store']);
        Route::put('/tags/{tag}', [TagController::class, 'update']);
        Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Seller Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['seller'])->group(function () {
        // Seller prompt management
        Route::post('/prompts', [PromptController::class, 'store']);
        Route::put('/prompts/{prompt}', [PromptController::class, 'update']);
        Route::delete('/prompts/{prompt}', [PromptController::class, 'destroy']);
        
        // Seller can view their own prompts (including drafts)
        Route::get('/seller/prompts', [PromptController::class, 'sellerPrompts']);
    });

    /*
    |--------------------------------------------------------------------------
    | Buyer Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['buyer'])->group(function () {
        // Purchase management
        Route::apiResource('purchases', PurchaseController::class)->except(['update', 'destroy']);
        
        // Buyer can access purchased prompt content
        Route::get('/prompts/{prompt}/content', [PromptController::class, 'getContent']);
    });

    /*
    |--------------------------------------------------------------------------
    | General Authenticated User Routes
    |--------------------------------------------------------------------------
    */
    
    // Reviews - authenticated users can create/manage reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
    
    // User's own reviews
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
    
    // User's purchases
    Route::get('/my-purchases', [PurchaseController::class, 'index']);
    
    // User's prompts (for sellers who are also buyers)
    Route::get('/my-prompts', [PromptController::class, 'myPrompts']);

    // Payment endpoints
    Route::post('/payments/create-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment']);
    Route::get('/payments/history', [PaymentController::class, 'getPaymentHistory']);
    Route::get('/payments/{payment}', [PaymentController::class, 'getPayment']);
});

/*
|--------------------------------------------------------------------------
| Search and Filter Routes
|--------------------------------------------------------------------------
|
| Advanced search functionality
|
*/

// Advanced prompt search (public)
Route::get('/search/prompts', [PromptController::class, 'search']);

// Category-based prompt filtering (public)
Route::get('/categories/{category}/prompts', [CategoryController::class, 'prompts']);

// Tag-based prompt filtering (public)
Route::get('/tags/{tag}/prompts', [TagController::class, 'prompts']);

/*
|--------------------------------------------------------------------------
| Statistics and Analytics Routes
|--------------------------------------------------------------------------
|
| Public statistics for the platform
|
*/

Route::get('/stats/platform', function () {
    return response()->json([
        'success' => true,
        'data' => [
            'total_prompts' => \App\Models\Prompt::published()->count(),
            'total_categories' => \App\Models\Category::where('is_active', true)->count(),
            'total_tags' => \App\Models\Tag::count(),
            'total_reviews' => \App\Models\Review::approved()->count(),
        ]
    ]);
});

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| Webhook endpoints that don't require authentication
|
*/

// Stripe webhook endpoint
Route::post('/webhooks/stripe', [PaymentController::class, 'handleWebhook']);
