<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\PromptController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Homepage
Route::get('/', [HomepageController::class, 'index'])->name('home');

// Public prompt browsing
Route::get('/prompts', [PromptController::class, 'index'])->name('prompts.index');
Route::get('/prompts/{prompt:uuid}', [PromptController::class, 'show'])->name('prompts.show');

// Category browsing
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Seller routes
    Route::middleware(['role:seller'])->group(function () {
        // Prompt management
        Route::get('/dashboard/prompts', [PromptController::class, 'manage'])->name('prompts.manage');
        Route::get('/prompts/create', [PromptController::class, 'create'])->name('prompts.create');
        Route::get('/prompts/{prompt:uuid}/edit', [PromptController::class, 'edit'])->name('prompts.edit');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
