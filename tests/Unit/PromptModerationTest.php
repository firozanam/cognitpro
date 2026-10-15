<?php

namespace Tests\Unit;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Services\PromptService;
use App\Modules\User\Models\User;
use App\Notifications\PromptApprovedNotification;
use App\Notifications\PromptRejectedNotification;
use App\Policies\PromptPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PromptModerationTest extends TestCase
{
    use RefreshDatabase;

    protected PromptService $service;

    protected User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PromptService::class);
        $this->seller = User::factory()->create(['role' => 'seller']);
    }

    protected function makePrompt(array $attributes = []): Prompt
    {
        return Prompt::factory()->create(array_merge([
            'seller_id' => $this->seller->id,
            'category_id' => Category::factory()->create()->id,
            'status' => 'draft',
        ], $attributes));
    }

    #[Test]
    public function submit_moves_draft_to_pending(): void
    {
        $prompt = $this->makePrompt();

        $this->service->submitForApproval($prompt);

        $this->assertSame('pending', $prompt->fresh()->status);
    }

    #[Test]
    public function submit_rejects_non_draft_prompts(): void
    {
        $prompt = $this->makePrompt(['status' => 'approved']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only draft prompts');

        $this->service->submitForApproval($prompt);
    }

    #[Test]
    public function approve_moves_pending_to_approved_and_notifies_seller(): void
    {
        Notification::fake();

        $prompt = $this->makePrompt([
            'status' => 'rejected',
            'rejection_reason' => 'Not good enough',
        ]);

        $this->service->approvePrompt($prompt);

        $fresh = $prompt->fresh();
        $this->assertSame('approved', $fresh->status);
        $this->assertNull($fresh->rejection_reason);

        Notification::assertSentTo($this->seller, PromptApprovedNotification::class);
    }

    #[Test]
    public function reject_sets_reason_and_notifies_seller(): void
    {
        Notification::fake();

        $prompt = $this->makePrompt(['status' => 'pending']);

        $this->service->rejectPrompt($prompt, 'Spam content detected');

        $fresh = $prompt->fresh();
        $this->assertSame('rejected', $fresh->status);
        $this->assertSame('Spam content detected', $fresh->rejection_reason);

        Notification::assertSentTo($this->seller, PromptRejectedNotification::class);
    }

    #[Test]
    public function toggle_featured_flips_the_flag(): void
    {
        $prompt = $this->makePrompt(['status' => 'approved', 'featured' => false]);

        $this->service->toggleFeatured($prompt);
        $this->assertTrue($prompt->fresh()->featured);

        $this->service->toggleFeatured($prompt->fresh());
        $this->assertFalse($prompt->fresh()->featured);
    }

    #[Test]
    public function create_prompt_generates_unique_slug_and_normalizes_free_pricing(): void
    {
        $category = Category::factory()->create();

        $prompt = $this->service->createPrompt([
            'seller_id' => $this->seller->id,
            'title' => 'Slug Source Title',
            'description' => 'd',
            'content' => 'c',
            'category_id' => $category->id,
            'ai_model' => 'gpt-4',
            'pricing_model' => 'free',
            'price' => 5,
            'tags' => ['one', 'two'],
        ]);

        $this->assertSame('slug-source-title', $prompt->slug);
        $this->assertSame(0.0, (float) $prompt->price);
        $this->assertNull($prompt->min_price);
        $this->assertSame(['one', 'two'], $prompt->tags->pluck('name')->all());

        $second = $this->service->createPrompt([
            'seller_id' => $this->seller->id,
            'title' => 'Slug Source Title',
            'description' => 'd',
            'content' => 'c',
            'category_id' => $category->id,
            'ai_model' => 'gpt-4',
            'pricing_model' => 'free',
        ]);

        $this->assertSame('slug-source-title-1', $second->slug);
    }

    #[Test]
    public function policy_allows_owner_and_admin_to_update_but_not_others(): void
    {
        $policy = new PromptPolicy;
        $owner = $this->seller;
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $prompt = $this->makePrompt();

        $this->assertTrue($policy->update($owner, $prompt));
        $this->assertTrue($policy->update($admin, $prompt));
        $this->assertFalse($policy->update($buyer, $prompt));

        $this->assertTrue($policy->delete($owner, $prompt));
        $this->assertFalse($policy->delete($buyer, $prompt));
    }

    #[Test]
    public function policy_gates_moderation_actions_to_admins(): void
    {
        $policy = new PromptPolicy;
        $admin = User::factory()->create(['role' => 'admin']);
        $prompt = $this->makePrompt();

        $this->assertTrue($policy->approve($admin, $prompt));
        $this->assertFalse($policy->approve($this->seller, $prompt));

        $this->assertTrue($policy->feature($admin, $prompt));
        $this->assertFalse($policy->feature($this->seller, $prompt));
    }

    #[Test]
    public function policy_only_lets_sellers_create(): void
    {
        $policy = new PromptPolicy;

        $this->assertTrue($policy->create($this->seller));
        $this->assertTrue($policy->create(User::factory()->create(['role' => 'admin'])));
        $this->assertFalse($policy->create(User::factory()->create(['role' => 'buyer'])));
    }
}
