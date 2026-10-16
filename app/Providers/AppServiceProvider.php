<?php

namespace App\Providers;

use App\Listeners\MergeGuestCart;
use App\Modules\User\Providers\UserModuleServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register module service providers
        $this->app->register(UserModuleServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Move the guest session cart into the user's cart on login
        Event::listen(Login::class, MergeGuestCart::class);
    }
}
