<?php

namespace Tests\Unit;

use App\Modules\Payment\PaymentService;
use App\Modules\Prompt\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
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

        $this->paymentService = new PaymentService;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_detects_if_stripe_is_configured(): void
    {
        $this->assertTrue($this->paymentService->isConfigured());
    }

    #[Test]
    public function it_detects_if_stripe_is_not_configured(): void
    {
        config(['services.stripe.secret' => null]);
        config(['services.stripe.key' => null]);

        $service = new PaymentService;

        $this->assertFalse($service->isConfigured());
    }

    #[Test]
    public function it_returns_publishable_key(): void
    {
        $key = $this->paymentService->getPublishableKey();

        $this->assertEquals('pk_test_123', $key);
    }

    #[Test]
    public function it_throws_exception_when_creating_intent_without_configuration(): void
    {
        config(['services.stripe.secret' => null]);

        $service = new PaymentService;

        $purchase = Mockery::mock(Purchase::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment processing is not configured.');

        $service->createPaymentIntent($purchase);
    }

    #[Test]
    public function it_throws_exception_when_confirming_intent_without_configuration(): void
    {
        config(['services.stripe.secret' => null]);

        $service = new PaymentService;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment processing is not configured.');

        $service->confirmPaymentIntent('pi_test_123');
    }

    #[Test]
    public function it_throws_exception_when_processing_refund_without_configuration(): void
    {
        config(['services.stripe.secret' => null]);

        $service = new PaymentService;

        $purchase = Mockery::mock(Purchase::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment processing is not configured.');

        $service->processRefund($purchase);
    }

    #[Test]
    public function it_throws_exception_when_refunding_purchase_without_payment(): void
    {
        $purchase = new Purchase;
        $purchase->payment_id = null;
        $purchase->price = 10.00;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No payment found for this purchase.');

        $this->paymentService->processRefund($purchase);
    }

    #[Test]
    public function it_verifies_invalid_webhook_payload(): void
    {
        $result = $this->paymentService->verifyWebhookSignature(
            'invalid_payload',
            'invalid_signature',
            'whsec_test'
        );

        $this->assertNull($result);
    }

    #[Test]
    public function it_converts_price_to_cents(): void
    {
        // INT-06: (int) cast truncated — 19.99 became 1998 cents.
        $this->assertSame(1999, $this->paymentService->toCents(19.99));
        $this->assertSame(435, $this->paymentService->toCents(4.35));
        $this->assertSame(1050, $this->paymentService->toCents(10.50));
        $this->assertSame(0, $this->paymentService->toCents(0));
    }
}
