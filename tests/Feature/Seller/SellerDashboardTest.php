<?php

namespace Tests\Feature\Seller;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SellerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_dashboard_renders_with_matching_props(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();
        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
        ]);

        $this->actingAs($seller)->get('/seller/dashboard')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($prompt) {
                $page->component('seller/dashboard')
                    ->where('stats.total_prompts', 1)
                    ->where('prompts.data.0.id', $prompt->id)
                    ->has('stats.total_earnings')
                    ->has('stats.total_sales')
                    ->has('stats.total_views')
                    ->has('stats.average_rating')
                    ->has('stats.pending_payout');
            });
    }

    public function test_prompt_delete_redirects_instead_of_erroring(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();
        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($seller)->delete(route('prompts.destroy', $prompt))
            ->assertRedirect(route('seller.dashboard', absolute: false));

        $this->assertDatabaseMissing('prompts', ['id' => $prompt->id]);
    }
}
