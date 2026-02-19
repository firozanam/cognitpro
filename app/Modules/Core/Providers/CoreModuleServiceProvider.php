<?php

namespace App\Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;

class CoreModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
    }
}
