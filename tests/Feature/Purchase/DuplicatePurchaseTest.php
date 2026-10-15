<?php

namespace Tests\Feature\Purchase;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\Prompt\Services\PurchaseService;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicatePurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function paidPrompt(object $seller): Prompt
    {
        $category = Category::factory()->create();

        return Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'fixed',
            'price' => 10.00,
            'status' => 'approved',
            'purchases_count' => 0,
        ]);
    }

    public function test_repeated_purchase_attempts_produce_exactly_one_pending_row(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $prompt = $this->paidPrompt($seller);

        // Two back-to-back POSTs (e.g. a double-click or back-button retry) —
        // the buyer must not stack pending purchases (DB-02).
        $this->actingAs($buyer)->post(route('purchases.purchase', $prompt));
        $this->actingAs($buyer)->post(route('purchases.purchase', $prompt));

        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseHas('purchases', [
            'buyer_id' => $buyer->id,
            'prompt_id' => $prompt->id,
            'status' => 'pending',
            'price' => 10.00,
        ]);
    }

    public function test_database_rejects_duplicate_buyer_prompt_rows(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $prompt = $this->paidPrompt($seller);

        $service = $this->app->make(PurchaseService::class);
        $service->initiatePurchase($buyer, $prompt);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        Purchase::create([
            'buyer_id' => $buyer->id,
            'prompt_id' => $prompt->id,
            'price' => 10.00,
            'platform_fee' => 1.50,
            'seller_earnings' => 8.50,
            'payment_method' => null,
            'status' => 'pending',
        ]);
    }

    public function test_failed_purchase_can_be_retried_on_the_same_row(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $prompt = $this->paidPrompt($seller);

        $service = $this->app->make(PurchaseService::class);
        $first = $service->initiatePurchase($buyer, $prompt);
        $service->failPurchase($first, 'card_declined');

        $retry = $service->initiatePurchase($buyer, $prompt);

        $this->assertDatabaseCount('purchases', 1);
        $this->assertTrue($retry->isPending());
        $this->assertSame($first->id, $retry->id);
    }
}
