<?php

namespace Tests\Feature\Moderation;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ModerationPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_moderation_pipeline_from_create_to_marketplace(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        // Seller submits for review at creation time
        $this->actingAs($seller)->post(route('prompts.store'), [
            'title' => 'Moderation Pipeline Prompt',
            'description' => 'A prompt submitted through the moderation pipeline.',
            'content' => 'You are a helpful assistant.',
            'category_id' => $category->id,
            'ai_model' => 'gpt-4',
            'pricing_model' => 'fixed',
            'price' => 19.99,
            'status' => 'pending',
        ]);

        $prompt = Prompt::where('title', 'Moderation Pipeline Prompt')->first();
        $this->assertNotNull($prompt);
        $this->assertSame('pending', $prompt->status);

        // The prompt appears in the admin pending queue
        $this->actingAs($admin)->get('/admin/prompts?status=pending')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($prompt) {
                $page->component('admin/prompts/index')
                    ->where('prompts.data.0.id', $prompt->id);
            });

        // Admin approves it
        $this->actingAs($admin)->post(route('admin.prompts.approve', $prompt->id));

        $this->assertDatabaseHas('prompts', [
            'id' => $prompt->id,
            'status' => 'approved',
        ]);

        // The approved prompt is visible on the marketplace
        $this->get('/marketplace')
            ->assertOk()
            ->assertSee('Moderation Pipeline Prompt');
    }

    public function test_store_defaults_to_draft_when_no_status_submitted(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();

        $this->actingAs($seller)->post(route('prompts.store'), [
            'title' => 'Draft By Default',
            'description' => 'Should stay a draft.',
            'content' => 'Content.',
            'category_id' => $category->id,
            'ai_model' => 'gpt-4',
            'pricing_model' => 'free',
        ]);

        $this->assertDatabaseHas('prompts', [
            'title' => 'Draft By Default',
            'status' => 'draft',
        ]);
    }

    public function test_store_rejects_status_outside_draft_and_pending(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();

        $this->actingAs($seller)->post(route('prompts.store'), [
            'title' => 'Invalid Status',
            'description' => 'Should fail validation.',
            'content' => 'Content.',
            'category_id' => $category->id,
            'ai_model' => 'gpt-4',
            'pricing_model' => 'free',
            'status' => 'approved',
        ])->assertInvalid('status');

        $this->assertDatabaseMissing('prompts', [
            'title' => 'Invalid Status',
        ]);
    }

    public function test_submit_route_transitions_draft_to_pending(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();
        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'draft',
        ]);

        $this->actingAs($seller)->post(route('prompts.submit', $prompt));

        $this->assertDatabaseHas('prompts', [
            'id' => $prompt->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_prompt_list_includes_non_approved_prompts_without_status_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();

        $draft = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Unfiltered Draft',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)->get('/admin/prompts')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($draft) {
                $page->component('admin/prompts/index')
                    ->where('prompts.data.0.id', $draft->id);
            });
    }

    public function test_admin_prompt_list_searches_by_title(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();

        $matching = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'UniqueSearchable Alpha',
            'status' => 'pending',
        ]);
        Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Unrelated Beta',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->get('/admin/prompts?search=UniqueSearchable')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($matching) {
                $page->has('prompts.data', 1)
                    ->where('prompts.data.0.id', $matching->id);
            });
    }
}
