<?php

namespace Tests\Feature;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\Prompt\Models\Review;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PromptShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected Prompt $prompt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create(['role' => 'seller']);

        $category = Category::factory()->create();

        $this->prompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
        ]);
    }

    /**
     * Reviews belong to a purchase and one buyer can purchase a prompt
     * once, so each review gets its own buyer and purchase.
     */
    private function createReview(array $attributes = []): Review
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $purchase = Purchase::factory()->create([
            'buyer_id' => $buyer->id,
            'prompt_id' => $this->prompt->id,
        ]);

        return Review::factory()->create(array_merge([
            'prompt_id' => $this->prompt->id,
            'user_id' => $buyer->id,
            'purchase_id' => $purchase->id,
        ], $attributes));
    }

    #[Test]
    public function prompt_show_includes_reviews(): void
    {
        $this->createReview();
        $this->createReview();

        $this->get("/prompts/{$this->prompt->slug}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('prompts/show')
                ->has('prompt.reviews', 2)
                ->where('prompt.reviews.0.user.name', fn ($name) => is_string($name) && $name !== ''));
    }

    #[Test]
    public function prompt_show_caps_reviews_at_five(): void
    {
        $this->createReview();
        $this->createReview();
        $this->createReview();
        $this->createReview();
        $this->createReview();
        $this->createReview();
        $this->createReview();

        $this->get("/prompts/{$this->prompt->slug}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('prompts/show')
                ->has('prompt.reviews', 5));
    }

    #[Test]
    public function prompt_show_orders_reviews_by_helpfulness(): void
    {
        // Lowest helpful_count created first; it must not come back first
        $low = $this->createReview(['helpful_count' => 1]);
        $high = $this->createReview(['helpful_count' => 9]);

        $this->get("/prompts/{$this->prompt->slug}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('prompts/show')
                ->where('prompt.reviews.0.id', $high->id)
                ->where('prompt.reviews.1.id', $low->id));
    }

    #[Test]
    public function prompt_show_does_not_expose_reviewer_emails(): void
    {
        $this->createReview();

        $this->get("/prompts/{$this->prompt->slug}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('prompts/show')
                ->where('prompt.reviews.0.user', fn ($user) => ! array_key_exists('email', (array) $user)));
    }

    #[Test]
    public function prompt_show_without_reviews_has_empty_reviews_array(): void
    {
        $this->get("/prompts/{$this->prompt->slug}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('prompts/show')
                ->has('prompt.reviews', 0));
    }
}
