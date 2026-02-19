<?php

namespace App\Modules\User\Providers;

use App\Modules\User\Contracts\UserRepositoryInterface;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class UserModuleServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register module routes
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
    }
}
