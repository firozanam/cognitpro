<?php

namespace App\Modules\Prompt\Providers;

use App\Modules\Prompt\Contracts\PromptRepositoryInterface;
use App\Modules\Prompt\Contracts\PurchaseRepositoryInterface;
use App\Modules\Prompt\Contracts\ReviewRepositoryInterface;
use App\Modules\Prompt\Repositories\PromptRepository;
use App\Modules\Prompt\Repositories\PurchaseRepository;
use App\Modules\Prompt\Repositories\ReviewRepository;
use App\Modules\Prompt\Services\PromptService;
use App\Modules\Prompt\Services\PurchaseService;
use App\Modules\Prompt\Services\ReviewService;
use Illuminate\Support\ServiceProvider;

class PromptModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(PromptRepositoryInterface::class, PromptRepository::class);
        $this->app->bind(PurchaseRepositoryInterface::class, PurchaseRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);

        // Bind services
        $this->app->bind(PromptService::class, function ($app) {
            return new PromptService(
                $app->make(PromptRepositoryInterface::class)
            );
        });

        $this->app->bind(PurchaseService::class, function ($app) {
            return new PurchaseService(
                $app->make(PurchaseRepositoryInterface::class),
                $app->make(PromptRepositoryInterface::class)
            );
        });

        $this->app->bind(ReviewService::class, function ($app) {
            return new ReviewService(
                $app->make(ReviewRepositoryInterface::class),
                $app->make(PurchaseRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load module routes
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
    }
}
