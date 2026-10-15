<?php

namespace Tests\Feature;

use App\Modules\Prompt\Models\Cart;
use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;

    protected User $seller;

    protected Prompt $prompt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create(['role' => 'seller']);
        $this->buyer = User::factory()->create(['role' => 'buyer']);

        $category = Category::factory()->create();

        $this->prompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'price' => 9.99,
        ]);
    }

    #[Test]
    public function guest_is_redirected_from_cart(): void
    {
        $this->get('/cart')->assertRedirect('/login');
    }

    #[Test]
    public function buyer_views_empty_cart(): void
    {
        $this->actingAs($this->buyer)->get('/cart')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->component('cart/index')
                    ->has('cartItems', 0)
                    ->where('total', 0)
                    ->where('count', 0);
            });
    }

    #[Test]
    public function buyer_can_add_prompt_to_cart(): void
    {
        $this->actingAs($this->buyer)
            ->post("/cart/add/{$this->prompt->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
        ]);
    }

    #[Test]
    public function seller_cannot_add_own_prompt_to_cart(): void
    {
        $this->actingAs($this->seller)
            ->post("/cart/add/{$this->prompt->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->seller->id,
            'prompt_id' => $this->prompt->id,
        ]);
    }

    #[Test]
    public function buyer_cannot_add_unapproved_prompt_to_cart(): void
    {
        $draftPrompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->buyer)
            ->post("/cart/add/{$draftPrompt->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $draftPrompt->id,
        ]);
    }

    #[Test]
    public function buyer_cannot_add_duplicate_cart_item(): void
    {
        Cart::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
        ]);

        $this->actingAs($this->buyer)
            ->post("/cart/add/{$this->prompt->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(1, Cart::where('user_id', $this->buyer->id)->count());
    }

    #[Test]
    public function buyer_can_remove_cart_item(): void
    {
        $item = Cart::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
        ]);

        $this->actingAs($this->buyer)
            ->delete("/cart/{$item->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('carts', [
            'id' => $item->id,
        ]);
    }

    #[Test]
    public function buyer_can_clear_cart(): void
    {
        $prompt2 = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'approved',
            'price' => 19.99,
        ]);

        Cart::create(['user_id' => $this->buyer->id, 'prompt_id' => $this->prompt->id]);
        Cart::create(['user_id' => $this->buyer->id, 'prompt_id' => $prompt2->id]);

        $this->actingAs($this->buyer)
            ->delete('/cart')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, Cart::where('user_id', $this->buyer->id)->count());
    }

    #[Test]
    public function cart_count_endpoint_returns_item_count(): void
    {
        Cart::create(['user_id' => $this->buyer->id, 'prompt_id' => $this->prompt->id]);

        $this->actingAs($this->buyer)
            ->getJson('/cart/count')
            ->assertOk()
            ->assertJson(['count' => 1]);
    }

    #[Test]
    public function cart_page_shows_total_across_items(): void
    {
        $prompt2 = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'approved',
            'price' => 19.99,
        ]);

        Cart::create(['user_id' => $this->buyer->id, 'prompt_id' => $this->prompt->id]);
        Cart::create(['user_id' => $this->buyer->id, 'prompt_id' => $prompt2->id]);

        $this->actingAs($this->buyer)->get('/cart')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->component('cart/index')
                    ->where('total', 29.98)
                    ->where('count', 2);
            });
    }
}
