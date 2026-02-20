<?php

use App\Modules\Admin\Http\Controllers\AdminDashboardController;
use App\Modules\Admin\Http\Controllers\AdminPromptController;
use App\Modules\Admin\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;

// Admin routes
Route::middleware(['web', 'auth', 'verified', 'admin'])->group(function () {
    // Dashboard
    Route::get('admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // User management
    Route::get('admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::put('admin/users/{id}/role', [AdminUserController::class, 'updateRole'])->name('admin.users.update-role');
    Route::post('admin/users/{id}/ban', [AdminUserController::class, 'ban'])->name('admin.users.ban');
    Route::post('admin/users/{id}/unban', [AdminUserController::class, 'unban'])->name('admin.users.unban');

    // Prompt moderation
    Route::get('admin/prompts', [AdminPromptController::class, 'index'])->name('admin.prompts.index');
    Route::post('admin/prompts/{id}/approve', [AdminPromptController::class, 'approve'])->name('admin.prompts.approve');
    Route::post('admin/prompts/{id}/reject', [AdminPromptController::class, 'reject'])->name('admin.prompts.reject');
    Route::post('admin/prompts/{id}/featured', [AdminPromptController::class, 'toggleFeatured'])->name('admin.prompts.featured');
});
