<?php

namespace Tests\Feature;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $seller;

    protected User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->seller = User::factory()->create(['role' => 'seller']);
        $this->buyer = User::factory()->create(['role' => 'buyer']);
    }

    /** @test */
    public function guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->getJson('/admin/dashboard');

        $response->assertStatus(401);
    }

    /** @test */
    public function non_admin_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->buyer)
            ->getJson('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_users',
                'total_sellers',
                'total_prompts',
                'total_sales',
                'total_revenue',
                'pending_prompts',
                'recent_users',
                'recent_purchases',
            ]);
    }

    /** @test */
    public function admin_can_list_users(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
            ]);
    }

    /** @test */
    public function admin_can_view_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/admin/users/{$this->seller->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->seller->id,
                'email' => $this->seller->email,
            ]);
    }

    /** @test */
    public function admin_can_update_user_role(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/users/{$this->buyer->id}", [
                'role' => 'seller',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $this->buyer->id,
            'role' => 'seller',
        ]);
    }

    /** @test */
    public function admin_can_ban_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/admin/users/{$this->seller->id}/ban");

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $this->seller->id,
            'banned_at' => now(),
        ]);
    }

    /** @test */
    public function admin_can_unban_user(): void
    {
        $this->seller->update(['banned_at' => now()]);

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/users/{$this->seller->id}/unban");

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $this->seller->id,
            'banned_at' => null,
        ]);
    }

    /** @test */
    public function admin_can_list_pending_prompts(): void
    {
        $category = Category::factory()->create();
        
        Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/prompts/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
            ]);
    }

    /** @test */
    public function admin_can_approve_prompt(): void
    {
        $category = Category::factory()->create();
        
        $prompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/prompts/{$prompt->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('prompts', [
            'id' => $prompt->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function admin_can_reject_prompt(): void
    {
        $category = Category::factory()->create();
        
        $prompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/prompts/{$prompt->id}/reject", [
                'reason' => 'Does not meet quality standards',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('prompts', [
            'id' => $prompt->id,
            'status' => 'rejected',
            'rejection_reason' => 'Does not meet quality standards',
        ]);
    }

    /** @test */
    public function admin_can_feature_prompt(): void
    {
        $category = Category::factory()->create();
        
        $prompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'featured' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/prompts/{$prompt->id}/feature");

        $response->assertStatus(200);

        $this->assertDatabaseHas('prompts', [
            'id' => $prompt->id,
            'featured' => true,
        ]);
    }

    /** @test */
    public function admin_cannot_modify_own_role(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/users/{$this->admin->id}", [
                'role' => 'buyer',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_cannot_ban_another_admin(): void
    {
        $anotherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/users/{$anotherAdmin->id}/ban");

        $response->assertStatus(403);
    }
}
