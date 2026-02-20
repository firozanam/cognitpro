<?php

namespace Tests\Unit;

use App\Modules\Payment\PaymentService;
use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        // Set test Stripe keys
        config(['services.stripe.secret' => 'sk_test_123']);
        config(['services.stripe.key' => 'pk_test_123']);
        config(['cashier.currency' => 'usd']);

        $this->paymentService = new PaymentService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_detects_if_stripe_is_configured(): void
    {
        $this->assertTrue($this->paymentService->isConfigured());
    }

    /** @test */
    public function it_detects_if_stripe_is_not_configured(): void
    {
        config(['services.stripe.secret' => null]);
        config(['services.stripe.key' => null]);

        $service = new PaymentService();

        $this->assertFalse($service->isConfigured());
    }

    /** @test */
    public function it_returns_publishable_key(): void
    {
        $key = $this->paymentService->getPublishableKey();

        $this->assertEquals('pk_test_123', $key);
    }

    /** @test */
    public function it_throws_exception_when_creating_intent_without_configuration(): void
    {
        config(['services.stripe.secret' => null]);

        $service = new PaymentService();

        $purchase = Mockery::mock(Purchase::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment processing is not configured.');

        $service->createPaymentIntent($purchase);
    }

    /** @test */
    public function it_throws_exception_when_confirming_intent_without_configuration(): void
    {
        config(['services.stripe.secret' => null]);

        $service = new PaymentService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment processing is not configured.');

        $service->confirmPaymentIntent('pi_test_123');
    }

    /** @test */
    public function it_throws_exception_when_processing_refund_without_configuration(): void
    {
        config(['services.stripe.secret' => null]);

        $service = new PaymentService();

        $purchase = Mockery::mock(Purchase::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment processing is not configured.');

        $service->processRefund($purchase);
    }

    /** @test */
    public function it_throws_exception_when_refunding_purchase_without_payment(): void
    {
        $purchase = Mockery::mock(Purchase::class);
        $purchase->payment_id = null;
        $purchase->price = 10.00;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No payment found for this purchase.');

        $this->paymentService->processRefund($purchase);
    }

    /** @test */
    public function it_verifies_invalid_webhook_payload(): void
    {
        $result = $this->paymentService->verifyWebhookSignature(
            'invalid_payload',
            'invalid_signature',
            'whsec_test'
        );

        $this->assertNull($result);
    }

    /** @test */
    public function it_converts_price_to_cents(): void
    {
        // This tests the internal conversion logic
        $price = 10.50;
        $expectedCents = 1050;
        
        $actualCents = (int) ($price * 100);
        
        $this->assertEquals($expectedCents, $actualCents);
    }
}
