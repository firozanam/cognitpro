<?php

namespace Tests\Feature;

use App\Modules\Prompt\Models\Cart;
use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    /** @test */
    public function guest_cannot_access_cart(): void
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_view_empty_cart(): void
    {
        $response = $this->actingAs($this->buyer)
            ->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
                'total' => 0,
                'count' => 0,
            ]);
    }

    /** @test */
    public function user_can_add_item_to_cart(): void
    {
        $response = $this->actingAs($this->buyer)
            ->postJson('/api/cart/add', [
                'prompt_id' => $this->prompt->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Prompt added to cart',
            ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
        ]);
    }

    /** @test */
    public function user_cannot_add_own_prompt_to_cart(): void
    {
        $response = $this->actingAs($this->seller)
            ->postJson('/api/cart/add', [
                'prompt_id' => $this->prompt->id,
            ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function user_cannot_add_unapproved_prompt_to_cart(): void
    {
        $draftPrompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->buyer)
            ->postJson('/api/cart/add', [
                'prompt_id' => $draftPrompt->id,
            ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function user_cannot_add_duplicate_item_to_cart(): void
    {
        Cart::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
        ]);

        $response = $this->actingAs($this->buyer)
            ->postJson('/api/cart/add', [
                'prompt_id' => $this->prompt->id,
            ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function user_can_remove_item_from_cart(): void
    {
        Cart::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
        ]);

        $response = $this->actingAs($this->buyer)
            ->deleteJson('/api/cart/remove', [
                'prompt_id' => $this->prompt->id,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
        ]);
    }

    /** @test */
    public function user_can_clear_cart(): void
    {
        $prompt2 = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'approved',
        ]);

        Cart::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
        ]);

        Cart::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt2->id,
        ]);

        $response = $this->actingAs($this->buyer)
            ->deleteJson('/api/cart/clear');

        $response->assertStatus(200);

        $this->assertEquals(0, Cart::where('user_id', $this->buyer->id)->count());
    }

    /** @test */
    public function cart_returns_correct_total(): void
    {
        $prompt2 = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'approved',
            'price' => 19.99,
        ]);

        Cart::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
        ]);

        Cart::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt2->id,
        ]);

        $response = $this->actingAs($this->buyer)
            ->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJson([
                'total' => 29.98,
                'count' => 2,
            ]);
    }
}
