<?php

namespace Tests\Feature;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    #[Test]
    public function non_admin_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->buyer)->get('/admin')->assertForbidden();
    }

    #[Test]
    public function admin_dashboard_renders_with_stats_props(): void
    {
        $this->actingAs($this->admin)->get('/admin')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->component('admin/dashboard')
                    ->has('stats.total_users')
                    ->has('stats.total_sellers')
                    ->has('stats.total_prompts')
                    ->has('stats.total_sales')
                    ->has('stats.total_revenue')
                    ->has('stats.pending_prompts')
                    ->has('recentUsers')
                    ->has('recentPurchases')
                    ->has('pendingPrompts');
            });
    }

    #[Test]
    public function admin_user_list_renders(): void
    {
        $this->actingAs($this->admin)->get('/admin/users')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->component('admin/users/index')
                    ->has('users.data', 3);
            });
    }

    #[Test]
    public function admin_can_ban_user(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/users/{$this->seller->id}/ban")
            ->assertRedirect();

        $this->assertNotNull($this->seller->fresh()->banned_at);
    }

    #[Test]
    public function banned_user_actually_gets_blocked(): void
    {
        // The ban must bite (BIZ-03): the session dies on the next request.
        // A real session re-reads the user from the database, so refresh the
        // in-memory instance actingAs would otherwise reuse.
        $this->actingAs($this->admin)
            ->post("/admin/users/{$this->seller->id}/ban");

        $this->seller->refresh();

        $this->actingAs($this->seller)->get('/dashboard')->assertRedirect('/login');
    }

    #[Test]
    public function admin_can_unban_user(): void
    {
        $this->seller->update(['banned_at' => now()]);

        $this->actingAs($this->admin)
            ->post("/admin/users/{$this->seller->id}/unban")
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->seller->id,
            'banned_at' => null,
        ]);
    }

    #[Test]
    public function admin_cannot_ban_another_admin(): void
    {
        $anotherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($this->admin)
            ->post("/admin/users/{$anotherAdmin->id}/ban")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $anotherAdmin->id,
            'banned_at' => null,
        ]);
    }

    #[Test]
    public function admin_cannot_modify_own_role(): void
    {
        $this->actingAs($this->admin)
            ->put("/admin/users/{$this->admin->id}/role", ['role' => 'buyer'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'role' => 'admin',
        ]);
    }

    #[Test]
    public function admin_moderation_list_renders_across_statuses(): void
    {
        $category = Category::factory()->create();

        Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)->get('/admin/prompts')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->component('admin/prompts/index')
                    ->has('prompts.data', 1);
            });
    }

    #[Test]
    public function admin_can_reject_prompt_with_reason(): void
    {
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/prompts/{$prompt->id}/reject", [
                'reason' => 'Does not meet quality standards',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('prompts', [
            'id' => $prompt->id,
            'status' => 'rejected',
            'rejection_reason' => 'Does not meet quality standards',
        ]);
    }

    #[Test]
    public function admin_can_toggle_prompt_featured(): void
    {
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'featured' => false,
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/prompts/{$prompt->id}/featured")
            ->assertRedirect();

        $this->assertDatabaseHas('prompts', [
            'id' => $prompt->id,
            'featured' => true,
        ]);
    }
}
