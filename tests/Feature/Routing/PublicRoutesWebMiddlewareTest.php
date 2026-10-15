<?php

namespace Tests\Feature\Routing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PublicRoutesWebMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Public module routes must run inside the web group so sessions,
     * Inertia shared props, asset versioning, and route-model binding work
     * (RTE-03). The Stripe webhook stays outside it (CSRF-free).
     */
    public function test_public_routes_carry_web_middleware(): void
    {
        $publicRouteNames = [
            'home',
            'marketplace.index',
            'marketplace.search',
            'marketplace.featured',
            'prompts.reviews.index',
            'prompts.show',
        ];

        foreach ($publicRouteNames as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Route [{$name}] must exist.");
            $this->assertContains('web', $route->gatherMiddleware(), "Route [{$name}] must run inside the web middleware group.");
        }
    }

    public function test_stripe_webhook_stays_outside_web_middleware(): void
    {
        $route = Route::getRoutes()->getByName('stripe.webhook');

        $this->assertNotNull($route);
        $this->assertNotContains('web', $route->gatherMiddleware());
    }

    public function test_public_marketplace_shares_inertia_props(): void
    {
        $this->get(route('marketplace.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('auth.user')
                ->has('name')
                ->has('sidebarOpen'));
    }
}
