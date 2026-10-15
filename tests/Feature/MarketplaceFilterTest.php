<?php

namespace Tests\Feature;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketplaceFilterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function search_param_narrows_marketplace_results(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();

        $match = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'title' => 'Needle Alpha Prompt',
        ]);
        Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'title' => 'Unrelated Beta Prompt',
        ]);

        $this->get('/marketplace?search=Needle')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($match) {
                $page->component('marketplace/index')
                    ->has('prompts.data', 1)
                    ->where('prompts.data.0.id', $match->id);
            });
    }

    #[Test]
    public function featured_param_returns_featured_prompts_only(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();

        $featured = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'featured' => true,
        ]);
        Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'featured' => false,
        ]);

        $this->get('/marketplace?featured=1')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($featured) {
                $page->has('prompts.data', 1)
                    ->where('prompts.data.0.id', $featured->id);
            });
    }

    #[Test]
    public function non_approved_prompts_never_appear_on_the_marketplace(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();

        Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
        ]);
        Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'pending',
            'title' => 'Pending Prompt Title',
        ]);

        $this->get('/marketplace')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->has('prompts.data', 1));
    }
}
