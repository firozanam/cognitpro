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

    private Prompt $prompt;

    protected function setUp(): void
    {
        parent::setUp();

        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($buyer);

        $this->prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'pay_what_you_want',
            'price' => 5.00,
            'min_price' => 1.00,
            'status' => 'approved',
        ]);
    }

    public function test_pay_what_you_want_prompt_enters_cart_with_chosen_price(): void
    {
        $this->post(route('cart.add', $this->prompt), ['price' => 7.5])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('carts', [
            'prompt_id' => $this->prompt->id,
            'price' => 7.5,
        ]);
    }

    public function test_pay_what_you_want_price_below_minimum_is_rejected(): void
    {
        $this->post(route('cart.add', $this->prompt), ['price' => 0.5])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('carts', [
            'prompt_id' => $this->prompt->id,
        ]);
    }

    public function test_pay_what_you_want_requires_a_price(): void
    {
        $this->post(route('cart.add', $this->prompt))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('carts', [
            'prompt_id' => $this->prompt->id,
        ]);
    }

    public function test_fixed_prompt_ignores_a_tampered_price(): void
    {
        $seller = User::factory()->create();
        $category = Category::factory()->create();

        $fixed = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'fixed',
            'price' => 9.99,
            'status' => 'approved',
        ]);

        $this->post(route('cart.add', $fixed), ['price' => 0.01])
            ->assertRedirect()
            ->assertSessionHas('success');

        // The buyer must not set their own price on fixed prompts
        $this->assertDatabaseHas('carts', [
            'prompt_id' => $fixed->id,
            'price' => 9.99,
        ]);
    }
}
