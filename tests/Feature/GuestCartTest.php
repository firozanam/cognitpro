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

class GuestCartTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected User $buyer;

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
            'pricing_model' => 'fixed',
            'price' => 9.99,
        ]);
    }

    #[Test]
    public function guest_can_add_prompt_to_cart(): void
    {
        $this->post("/cart/add/{$this->prompt->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame([$this->prompt->id], session('guest_cart'));
        $this->assertDatabaseCount('carts', 0);
    }

    #[Test]
    public function guest_cannot_add_unapproved_prompt(): void
    {
        $draftPrompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'draft',
        ]);

        $this->post("/cart/add/{$draftPrompt->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(session('guest_cart'));
    }

    #[Test]
    public function guest_cannot_add_duplicate_prompt(): void
    {
        $this->post("/cart/add/{$this->prompt->id}")->assertRedirect();

        $this->post("/cart/add/{$this->prompt->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame([$this->prompt->id], session('guest_cart'));
    }

    #[Test]
    public function guest_views_cart_with_session_items(): void
    {
        $prompt2 = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'approved',
            'price' => 19.99,
        ]);

        $this->withSession(['guest_cart' => [$this->prompt->id, $prompt2->id]])
            ->get('/cart')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('cart/index')
                ->has('cartItems', 2)
                ->where('total', 29.98)
                ->where('count', 2));
    }

    #[Test]
    public function guest_cart_hides_unapproved_prompts(): void
    {
        $draftPrompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'draft',
        ]);

        $this->withSession(['guest_cart' => [$this->prompt->id, $draftPrompt->id]])
            ->get('/cart')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('cart/index')
                ->has('cartItems', 1)
                ->where('total', 9.99)
                ->where('count', 1));
    }

    #[Test]
    public function guest_can_remove_cart_item(): void
    {
        $prompt2 = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'approved',
        ]);

        $this->withSession(['guest_cart' => [$this->prompt->id, $prompt2->id]])
            ->delete("/cart/{$prompt2->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame([$this->prompt->id], session('guest_cart'));
    }

    #[Test]
    public function guest_can_clear_cart(): void
    {
        $this->withSession(['guest_cart' => [$this->prompt->id]])
            ->delete('/cart')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull(session('guest_cart'));
    }

    #[Test]
    public function guest_checkout_redirects_to_login(): void
    {
        $this->post('/cart/checkout')->assertRedirect('/login');
    }

    #[Test]
    public function login_merges_guest_cart_into_user_cart(): void
    {
        $prompt2 = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'approved',
            'price' => 19.99,
        ]);

        $draftPrompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'draft',
        ]);

        // prompt2 is already in the buyer's cart and must not duplicate.
        Cart::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt2->id,
        ]);

        $this->withSession(['guest_cart' => [$this->prompt->id, $prompt2->id, $draftPrompt->id]])
            ->post('/login', [
                'email' => $this->buyer->email,
                'password' => 'password',
            ])
            ->assertRedirect();

        // Only the approved, not-yet-owned prompt is merged.
        $this->assertSame(2, Cart::where('user_id', $this->buyer->id)->count());
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
            'price' => 9.99,
        ]);
        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $draftPrompt->id,
        ]);
        $this->assertNull(session('guest_cart'));
    }

    #[Test]
    public function guest_cart_count_is_shared(): void
    {
        $draftPrompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'draft',
        ]);

        $this->withSession(['guest_cart' => [$this->prompt->id, $draftPrompt->id]])
            ->get('/cart')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('cartCount', 1));
    }
}
