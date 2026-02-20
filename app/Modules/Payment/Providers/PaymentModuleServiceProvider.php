<?php

namespace App\Modules\Payment\Providers;

use App\Modules\Payment\PaymentService;
use Illuminate\Support\ServiceProvider;

class PaymentModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load module routes
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
    }
}
