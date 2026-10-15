<?php

namespace Tests\Feature\Cart;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function promptFor(User $seller, float $price): Prompt
    {
        $category = Category::factory()->create();

        return Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => $price === 0.0 ? 'free' : 'fixed',
            'price' => $price,
            'status' => 'approved',
            'purchases_count' => 0,
        ]);
    }

    public function test_checkout_completes_free_items_and_redirects_paid_items_to_payment(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $free = $this->promptFor($seller, 0);
        $paid = $this->promptFor($seller, 10);

        $this->actingAs($buyer)->post(route('cart.add', $free));
        $this->actingAs($buyer)->post(route('cart.add', $paid));

        $response = $this->actingAs($buyer)->post(route('cart.checkout'));

        $this->assertDatabaseHas('purchases', [
            'buyer_id' => $buyer->id,
            'prompt_id' => $free->id,
            'status' => 'completed',
            'payment_method' => 'free',
        ]);
        $this->assertDatabaseHas('purchases', [
            'buyer_id' => $buyer->id,
            'prompt_id' => $paid->id,
            'status' => 'pending',
        ]);

        // Cart is emptied on checkout (STM-01)
        $this->assertDatabaseMissing('carts', ['user_id' => $buyer->id]);

        $pendingPurchaseId = \App\Modules\Prompt\Models\Purchase::where('prompt_id', $paid->id)->value('id');
        $response->assertRedirect(route('payment.stripe.show', $pendingPurchaseId));
    }

    public function test_checkout_with_only_free_items_redirects_to_purchases(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $free = $this->promptFor($seller, 0);
        $this->actingAs($buyer)->post(route('cart.add', $free));

        $this->actingAs($buyer)->post(route('cart.checkout'))
            ->assertRedirect(route('purchases.index'));

        $this->assertDatabaseHas('purchases', [
            'buyer_id' => $buyer->id,
            'prompt_id' => $free->id,
            'status' => 'completed',
        ]);
    }

    public function test_checkout_with_empty_cart_redirects_back_with_error(): void
    {
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->post(route('cart.checkout'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');
    }
}
