<?php

namespace Tests\Feature\Purchase;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreePurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_prompt_purchase_persists_and_completes(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'free',
            'price' => 0,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($buyer)->post(route('purchases.purchase', $prompt));

        $response->assertRedirect(route('purchases.show', 1));

        $this->assertDatabaseHas('purchases', [
            'buyer_id' => $buyer->id,
            'prompt_id' => $prompt->id,
            'status' => 'completed',
            'payment_method' => 'free',
        ]);
    }

    public function test_paid_prompt_purchase_stays_pending_for_payment(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'fixed',
            'price' => 9.99,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($buyer)->post(route('purchases.purchase', $prompt));

        // Paid prompts must never complete without payment: the purchase
        // stays pending and the buyer is sent to the payment page
        $response->assertRedirect(route('payment.stripe.show', 1));

        $this->assertDatabaseHas('purchases', [
            'buyer_id' => $buyer->id,
            'prompt_id' => $prompt->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('purchases', [
            'buyer_id' => $buyer->id,
            'prompt_id' => $prompt->id,
            'status' => 'completed',
        ]);
    }

    public function test_zero_price_prompt_is_treated_as_free(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'fixed',
            'price' => 0,
            'status' => 'approved',
        ]);

        $this->actingAs($buyer)->post(route('purchases.purchase', $prompt))
            ->assertRedirect(route('purchases.show', 1));

        $this->assertDatabaseHas('purchases', [
            'buyer_id' => $buyer->id,
            'prompt_id' => $prompt->id,
            'status' => 'completed',
            'payment_method' => 'free',
        ]);
    }
}
