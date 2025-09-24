<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $seller;
    protected User $buyer;
    protected Category $category;
    protected Prompt $prompt;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->seller = User::create([
            'name' => 'Test Seller',
            'email' => 'seller@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'email_verified_at' => now(),
        ]);

        $this->buyer = User::create([
            'name' => 'Test Buyer',
            'email' => 'buyer@test.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
            'email_verified_at' => now(),
        ]);

        // Create test category
        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category description',
            'color' => '#3B82F6',
            'icon' => '📝',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Create test prompt
        $this->prompt = Prompt::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Test Prompt',
            'description' => 'Test prompt description',
            'content' => 'Test prompt content',
            'excerpt' => 'Test excerpt',
            'price' => 9.99,
            'price_type' => 'fixed',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_create_payment_intent_success()
    {
        $response = $this->actingAs($this->buyer)
            ->postJson('/api/payments/create-intent', [
                'prompt_id' => $this->prompt->id,
                'amount' => 9.99,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'payment_intent_id',
                    'client_secret',
                    'amount',
                    'currency',
                    'publishable_key',
                ],
            ]);

        $this->assertTrue($response->json('success'));
    }

    public function test_create_payment_intent_validation_fails()
    {
        $response = $this->actingAs($this->buyer)
            ->postJson('/api/payments/create-intent', [
                'prompt_id' => 999, // Non-existent prompt
                'amount' => 9.99,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);

        $this->assertFalse($response->json('success'));
    }

    public function test_buyer_cannot_purchase_own_prompt()
    {
        $response = $this->actingAs($this->seller)
            ->postJson('/api/payments/create-intent', [
                'prompt_id' => $this->prompt->id,
                'amount' => 9.99,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'You cannot purchase your own prompt',
            ]);
    }

    public function test_cannot_purchase_unpublished_prompt()
    {
        $this->prompt->update(['status' => 'draft']);

        $response = $this->actingAs($this->buyer)
            ->postJson('/api/payments/create-intent', [
                'prompt_id' => $this->prompt->id,
                'amount' => 9.99,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'This prompt is not available for purchase',
            ]);
    }

    public function test_invalid_amount_rejected()
    {
        $response = $this->actingAs($this->buyer)
            ->postJson('/api/payments/create-intent', [
                'prompt_id' => $this->prompt->id,
                'amount' => 5.99, // Wrong amount
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid amount',
            ]);
    }

    public function test_confirm_payment_success()
    {
        // First create a payment intent
        $createResponse = $this->actingAs($this->buyer)
            ->postJson('/api/payments/create-intent', [
                'prompt_id' => $this->prompt->id,
                'amount' => 9.99,
            ]);

        $paymentIntentId = $createResponse->json('data.payment_intent_id');

        // Then confirm the payment
        $response = $this->actingAs($this->buyer)
            ->postJson('/api/payments/confirm', [
                'payment_intent_id' => $paymentIntentId,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'purchase_id',
                    'prompt',
                    'amount_paid',
                ],
            ]);

        $this->assertTrue($response->json('success'));
        
        // Verify purchase was created
        $this->assertDatabaseHas('purchases', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
            'price_paid' => 9.99,
        ]);
    }

    public function test_get_payment_history()
    {
        // Create a payment first
        $this->actingAs($this->buyer)
            ->postJson('/api/payments/create-intent', [
                'prompt_id' => $this->prompt->id,
                'amount' => 9.99,
            ]);

        $response = $this->actingAs($this->buyer)
            ->getJson('/api/payments/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'amount',
                            'currency',
                            'payment_gateway',
                            'status',
                            'created_at',
                        ],
                    ],
                ],
            ]);

        $this->assertTrue($response->json('success'));
    }

    public function test_webhook_handling()
    {
        $webhookPayload = [
            'id' => 'evt_test_webhook',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_payment_intent',
                    'status' => 'succeeded',
                ],
            ],
        ];

        $response = $this->postJson('/api/webhooks/stripe', $webhookPayload, [
            'Stripe-Signature' => 'test_signature',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public function test_stripe_service_creates_payment_intent()
    {
        $stripeService = new StripeService();
        
        $result = $stripeService->createPaymentIntent($this->buyer, $this->prompt, 9.99);

        $this->assertArrayHasKey('payment_intent_id', $result);
        $this->assertArrayHasKey('client_secret', $result);
        $this->assertEquals(999, $result['amount']); // Amount in cents
        $this->assertEquals('usd', $result['currency']);
        
        // Verify payment record was created
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->buyer->id,
            'amount' => 9.99,
            'payment_gateway' => 'stripe',
            'status' => 'pending',
        ]);
    }

    public function test_stripe_service_confirms_payment()
    {
        $stripeService = new StripeService();
        
        // Create payment intent first
        $paymentIntent = $stripeService->createPaymentIntent($this->buyer, $this->prompt, 9.99);
        
        // Confirm payment
        $result = $stripeService->confirmPayment($paymentIntent['payment_intent_id']);

        $this->assertEquals('succeeded', $result['status']);
        $this->assertEquals($paymentIntent['payment_intent_id'], $result['payment_intent_id']);
        
        // Verify payment status was updated
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $paymentIntent['payment_intent_id'],
            'status' => 'completed',
        ]);
    }
}
