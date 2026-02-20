<?php

use App\Modules\Payment\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Payment routes (authenticated)
Route::middleware(['web', 'auth', 'verified'])->group(function () {
    Route::get('purchases/{purchase}/stripe-payment', [PaymentController::class, 'show'])->name('payment.stripe.show');
    Route::post('purchases/{purchase}/confirm-payment', [PaymentController::class, 'confirm'])->name('payment.stripe.confirm');
});

// Stripe webhook (no auth required)
Route::post('stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');
