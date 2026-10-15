<?php

namespace Tests\Feature\Cart;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayWhatYouWantCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_pay_what_you_want_prompts_cannot_enter_the_cart(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'pay_what_you_want',
            'price' => 5.00,
            'min_price' => 1.00,
            'status' => 'approved',
        ]);

        $this->actingAs($buyer)
            ->post(route('cart.add', $prompt))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('carts', [
            'user_id' => $buyer->id,
            'prompt_id' => $prompt->id,
        ]);
    }
}
