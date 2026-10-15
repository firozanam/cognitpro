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
}
