<?php

namespace Tests\Feature\Purchase;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Services\PurchaseService;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PromptContentDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected Prompt $prompt;

    protected User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $seller = User::factory()->create();
        $this->buyer = User::factory()->create();
        $category = Category::factory()->create();

        $this->prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'fixed',
            'price' => 10.00,
            'status' => 'approved',
            'content' => 'SECRET PROMPT BODY',
        ]);
    }

    public function test_completed_buyer_receives_prompt_content_on_purchase_show(): void
    {
        $purchase = $this->app->make(PurchaseService::class)->initiatePurchase($this->buyer, $this->prompt);
        $this->app->make(PurchaseService::class)->completePurchase($purchase, 'stripe', 'pi_test');

        $this->actingAs($this->buyer)
            ->get(route('purchases.show', $purchase->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('purchase.prompt.content', 'SECRET PROMPT BODY'));
    }

    public function test_completed_buyer_receives_prompt_content_on_prompt_show(): void
    {
        $purchase = $this->app->make(PurchaseService::class)->initiatePurchase($this->buyer, $this->prompt);
        $this->app->make(PurchaseService::class)->completePurchase($purchase, 'stripe', 'pi_test');

        $this->actingAs($this->buyer)
            ->get(route('prompts.show', $this->prompt->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('prompt.content', 'SECRET PROMPT BODY'));
    }

    public function test_pending_buyer_does_not_receive_prompt_content(): void
    {
        $purchase = $this->app->make(PurchaseService::class)->initiatePurchase($this->buyer, $this->prompt);

        $this->actingAs($this->buyer)
            ->get(route('purchases.show', $purchase->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->missing('purchase.prompt.content'));
    }

    public function test_guest_marketplace_listing_has_no_prompt_content(): void
    {
        $this->get(route('marketplace.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->missing('prompts.data.0.content'));
    }
}
