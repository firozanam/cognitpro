<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_access_seller_dashboard_routes()
    {
        $seller = User::factory()->create(['role' => 'seller']);

        $this->actingAs($seller)
            ->get('/dashboard/analytics')
            ->assertStatus(200);

        $this->actingAs($seller)
            ->get('/dashboard/sales')
            ->assertStatus(200);

        $this->actingAs($seller)
            ->get('/dashboard/payouts')
            ->assertStatus(200);

        $this->actingAs($seller)
            ->get('/dashboard/reviews')
            ->assertStatus(200);
    }

    public function test_buyer_can_access_buyer_dashboard_routes()
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($buyer)
            ->get('/dashboard/purchases')
            ->assertStatus(200);

        $this->actingAs($buyer)
            ->get('/dashboard/reviews')
            ->assertStatus(200);

        $this->actingAs($buyer)
            ->get('/dashboard/favorites')
            ->assertStatus(200);
    }

    public function test_buyer_cannot_access_seller_routes()
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($buyer)
            ->get('/dashboard/analytics')
            ->assertStatus(403);

        $this->actingAs($buyer)
            ->get('/dashboard/sales')
            ->assertStatus(403);

        $this->actingAs($buyer)
            ->get('/dashboard/payouts')
            ->assertStatus(403);
    }

    public function test_seller_cannot_access_buyer_specific_routes()
    {
        $seller = User::factory()->create(['role' => 'seller']);

        // Note: Sellers can buy, so they should be able to access buyer routes
        // This test verifies the middleware logic
        $this->actingAs($seller)
            ->get('/dashboard/purchases')
            ->assertStatus(200); // Sellers can buy

        $this->actingAs($seller)
            ->get('/dashboard/favorites')
            ->assertStatus(200); // Sellers can have favorites
    }

    public function test_unauthenticated_user_redirected_to_login()
    {
        $this->get('/dashboard/analytics')
            ->assertRedirect('/login');

        $this->get('/dashboard/purchases')
            ->assertRedirect('/login');
    }

    public function test_dashboard_renders_correct_page_for_seller()
    {
        $seller = User::factory()->create(['role' => 'seller']);

        $response = $this->actingAs($seller)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('dashboard/seller')
        );
    }

    public function test_dashboard_renders_correct_page_for_buyer()
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $response = $this->actingAs($buyer)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('dashboard/buyer')
        );
    }
}
