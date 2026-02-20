<?php

namespace Tests\Feature;

use App\Modules\Payment\PaymentService;
use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PaymentTest extends TestCase
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
    public function guest_cannot_access_payment(): void
    {
        $response = $this->postJson('/api/payment/create-intent', [
            'purchase_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_create_payment_intent(): void
    {
        $purchase = Purchase::factory()->create([
            'buyer_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
            'status' => 'pending',
            'price' => 9.99,
        ]);

        // Mock the PaymentService
        $mockService = Mockery::mock(PaymentService::class);
        $mockService->shouldReceive('createPaymentIntent')
            ->once()
            ->with($purchase)
            ->andReturn([
                'client_secret' => 'pi_test_secret',
                'payment_intent_id' => 'pi_test_123',
            ]);

        $this->app->instance(PaymentService::class, $mockService);

        $response = $this->actingAs($this->buyer)
            ->postJson('/api/payment/create-intent', [
                'purchase_id' => $purchase->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'client_secret',
                'payment_intent_id',
            ]);
    }

    /** @test */
    public function user_cannot_create_payment_intent_for_other_users_purchase(): void
    {
        $otherBuyer = User::factory()->create(['role' => 'buyer']);
        
        $purchase = Purchase::factory()->create([
            'buyer_id' => $otherBuyer->id,
            'prompt_id' => $this->prompt->id,
            'status' => 'pending',
            'price' => 9.99,
        ]);

        $response = $this->actingAs($this->buyer)
            ->postJson('/api/payment/create-intent', [
                'purchase_id' => $purchase->id,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_cannot_create_payment_intent_for_completed_purchase(): void
    {
        $purchase = Purchase::factory()->create([
            'buyer_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
            'status' => 'completed',
            'price' => 9.99,
        ]);

        $response = $this->actingAs($this->buyer)
            ->postJson('/api/payment/create-intent', [
                'purchase_id' => $purchase->id,
            ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function webhook_handles_successful_payment(): void
    {
        $purchase = Purchase::factory()->create([
            'buyer_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
            'status' => 'pending',
            'price' => 9.99,
        ]);

        // Mock the PaymentService
        $mockService = Mockery::mock(PaymentService::class);
        $mockService->shouldReceive('handleWebhook')
            ->once()
            ->andReturn(response()->json(['status' => 'success']));

        $this->app->instance(PaymentService::class, $mockService);

        $response = $this->postJson('/api/payment/webhook', [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'metadata' => [
                        'purchase_id' => $purchase->id,
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function free_purchase_bypasses_payment(): void
    {
        $freePrompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->prompt->category_id,
            'status' => 'approved',
            'pricing_model' => 'free',
            'price' => 0,
        ]);

        $purchase = Purchase::factory()->create([
            'buyer_id' => $this->buyer->id,
            'prompt_id' => $freePrompt->id,
            'status' => 'pending',
            'price' => 0,
        ]);

        // Free purchases should complete immediately without payment
        $response = $this->actingAs($this->buyer)
            ->postJson('/api/payment/free', [
                'purchase_id' => $purchase->id,
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'completed',
        ]);
    }
}
