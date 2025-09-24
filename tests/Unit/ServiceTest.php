<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected User $buyer;
    protected Category $category;
    protected Prompt $prompt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::create([
            'name' => 'Test Seller',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'email_verified_at' => now(),
        ]);

        $this->buyer = User::create([
            'name' => 'Test Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
            'email_verified_at' => now(),
        ]);

        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category description',
            'color' => '#3B82F6',
            'icon' => '📝',
            'is_active' => true,
            'sort_order' => 0,
        ]);

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

    public function test_payment_service_process_payment()
    {
        $paymentService = new PaymentService();

        $purchase = $paymentService->processPayment(
            $this->buyer,
            $this->prompt,
            9.99,
            'stripe',
            'txn_test_123'
        );

        // Test purchase was created
        $this->assertInstanceOf(Purchase::class, $purchase);
        $this->assertEquals($this->buyer->id, $purchase->user_id);
        $this->assertEquals($this->prompt->id, $purchase->prompt_id);
        $this->assertEquals(9.99, $purchase->price_paid);
        $this->assertEquals('stripe', $purchase->payment_gateway);
        $this->assertEquals('txn_test_123', $purchase->transaction_id);

        // Test payment record was created
        $payment = Payment::where('transaction_id', 'txn_test_123')->first();
        $this->assertNotNull($payment);
        $this->assertEquals($this->buyer->id, $payment->user_id);
        $this->assertEquals(9.99, $payment->amount);
        $this->assertEquals('USD', $payment->currency);
        $this->assertEquals('stripe', $payment->payment_gateway);
        $this->assertEquals('completed', $payment->status);
    }

    public function test_payment_service_commission_calculation()
    {
        $paymentService = new PaymentService();

        // Test default 10% commission
        $commission = $paymentService->calculateCommission(100.00);
        $this->assertEquals(10.00, $commission);

        $commission = $paymentService->calculateCommission(9.99);
        $this->assertEqualsWithDelta(0.999, $commission, 0.001); // Allow small floating point difference

        // Test seller payout calculation
        $payout = $paymentService->calculateSellerPayout(100.00);
        $this->assertEquals(90.00, $payout);

        $payout = $paymentService->calculateSellerPayout(9.99);
        $this->assertEqualsWithDelta(8.991, $payout, 0.001); // Allow small floating point difference
    }

    public function test_payment_service_refund()
    {
        $paymentService = new PaymentService();

        // Create a purchase first
        $purchase = $paymentService->processPayment(
            $this->buyer,
            $this->prompt,
            9.99,
            'stripe',
            'txn_test_refund'
        );

        // Process refund
        $result = $paymentService->processRefund($purchase, 'Customer requested refund');

        $this->assertTrue($result);

        // Check payment status was updated
        $payment = Payment::where('transaction_id', 'txn_test_refund')->first();
        $this->assertEquals('refunded', $payment->status);
        $this->assertArrayHasKey('refund_reason', $payment->metadata);
        $this->assertEquals('Customer requested refund', $payment->metadata['refund_reason']);
    }

    public function test_stripe_service_create_payment_intent()
    {
        $stripeService = new StripeService();

        $result = $stripeService->createPaymentIntent($this->buyer, $this->prompt, 9.99);

        // Test payment intent structure
        $this->assertArrayHasKey('payment_intent_id', $result);
        $this->assertArrayHasKey('client_secret', $result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertArrayHasKey('currency', $result);
        $this->assertArrayHasKey('payment_id', $result);

        // Test amount is in cents
        $this->assertEquals(999, $result['amount']);
        $this->assertEquals('usd', $result['currency']);

        // Test payment record was created
        $payment = Payment::where('transaction_id', $result['payment_intent_id'])->first();
        $this->assertNotNull($payment);
        $this->assertEquals($this->buyer->id, $payment->user_id);
        $this->assertEquals(9.99, $payment->amount);
        $this->assertEquals('pending', $payment->status);
    }

    public function test_stripe_service_confirm_payment()
    {
        $stripeService = new StripeService();

        // Create payment intent first
        $paymentIntent = $stripeService->createPaymentIntent($this->buyer, $this->prompt, 9.99);

        // Confirm payment
        $result = $stripeService->confirmPayment($paymentIntent['payment_intent_id']);

        $this->assertEquals('succeeded', $result['status']);
        $this->assertEquals($paymentIntent['payment_intent_id'], $result['payment_intent_id']);

        // Test payment status was updated
        $payment = Payment::where('transaction_id', $paymentIntent['payment_intent_id'])->first();
        $this->assertEquals('completed', $payment->status);
        $this->assertNotNull($payment->processed_at);
    }

    public function test_stripe_service_webhook_handling()
    {
        $stripeService = new StripeService();

        // Create a payment first
        $paymentIntent = $stripeService->createPaymentIntent($this->buyer, $this->prompt, 9.99);

        // Simulate webhook payload
        $webhookPayload = [
            'id' => 'evt_test_webhook',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => $paymentIntent['payment_intent_id'],
                    'status' => 'succeeded',
                ],
            ],
        ];

        $result = $stripeService->handleWebhook($webhookPayload, 'test_signature');

        $this->assertTrue($result);

        // Test payment status was updated via webhook
        $payment = Payment::where('transaction_id', $paymentIntent['payment_intent_id'])->first();
        $this->assertEquals('completed', $payment->status);
    }

    public function test_stripe_service_failed_payment_webhook()
    {
        $stripeService = new StripeService();

        // Create a payment first
        $paymentIntent = $stripeService->createPaymentIntent($this->buyer, $this->prompt, 9.99);

        // Simulate failed payment webhook
        $webhookPayload = [
            'id' => 'evt_test_webhook_failed',
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => $paymentIntent['payment_intent_id'],
                    'status' => 'failed',
                    'last_payment_error' => [
                        'message' => 'Your card was declined.',
                    ],
                ],
            ],
        ];

        $result = $stripeService->handleWebhook($webhookPayload, 'test_signature');

        $this->assertTrue($result);

        // Test payment status was updated to failed
        $payment = Payment::where('transaction_id', $paymentIntent['payment_intent_id'])->first();
        $this->assertEquals('failed', $payment->status);
        $this->assertArrayHasKey('failure_reason', $payment->metadata);
        $this->assertEquals('Your card was declined.', $payment->metadata['failure_reason']);
    }

    // Note: PromptService search method is not implemented yet

    // Note: PayoutService methods are not fully implemented yet

    public function test_stripe_service_get_publishable_key()
    {
        $stripeService = new StripeService();
        
        $key = $stripeService->getPublishableKey();
        
        // Should return mock key since Stripe is not configured
        $this->assertEquals('pk_test_mock_key', $key);
    }
}
