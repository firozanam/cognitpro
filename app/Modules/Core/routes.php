<?php

use App\Modules\Core\Http\Controllers\AppearanceController;
use App\Modules\Core\Http\Controllers\DashboardController;
use App\Modules\Core\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Public routes — inside the web group for sessions, Inertia shared
// props, and asset versioning (RTE-03)
Route::middleware('web')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
});

// Authenticated routes
Route::middleware(['web', 'auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('settings/appearance', [AppearanceController::class, 'edit'])->name('appearance.edit');
});

// Settings redirect
Route::middleware(['web', 'auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');
});
