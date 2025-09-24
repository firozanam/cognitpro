<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptCreateRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->get('/prompts/create');
        
        $response->assertRedirect('/login');
    }

    public function test_buyer_cannot_access_create_prompt_page()
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $response = $this->actingAs($buyer)->get('/prompts/create');
        
        $response->assertStatus(403);
    }

    public function test_seller_can_access_create_prompt_page()
    {
        // Create some test data
        Category::create(['name' => 'Test Category', 'slug' => 'test-category', 'is_active' => true]);
        Tag::create(['name' => 'Test Tag', 'slug' => 'test-tag']);

        $seller = User::factory()->create(['role' => 'seller']);

        $response = $this->actingAs($seller)->get('/prompts/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('prompts/create')
                ->has('categories')
                ->has('tags')
        );
    }

    public function test_admin_can_access_create_prompt_page()
    {
        // Create some test data
        Category::create(['name' => 'Test Category', 'slug' => 'test-category', 'is_active' => true]);
        Tag::create(['name' => 'Test Tag', 'slug' => 'test-tag']);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/prompts/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('prompts/create')
                ->has('categories')
                ->has('tags')
        );
    }
}
