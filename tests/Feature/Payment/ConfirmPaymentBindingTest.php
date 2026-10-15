<?php

namespace Tests\Feature\Payment;

use App\Modules\Payment\PaymentService;
use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Services\PurchaseService;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class ConfirmPaymentBindingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.secret' => 'sk_test_123', 'services.stripe.key' => 'pk_test_123']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function paidPrompt(object $seller): Prompt
    {
        $category = Category::factory()->create();

        return Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'fixed',
            'price' => 10.00,
            'status' => 'approved',
        ]);
    }

    protected function mockIntent(string $status, array $metadata, int $amount): PaymentService
    {
        $service = Mockery::mock(PaymentService::class)->makePartial();
        $service->shouldReceive('confirmPaymentIntent')
            ->andReturn(['status' => $status, 'amount' => $amount, 'currency' => 'usd', 'metadata' => $metadata]);

        $this->instance(PaymentService::class, $service);

        return $service;
    }

    public function test_confirm_completes_purchase_when_intent_matches(): void
    {
        Notification::fake();

        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $prompt = $this->paidPrompt($seller);

        $purchase = $this->app->make(PurchaseService::class)
            ->initiatePurchase($buyer, $prompt);

        $this->mockIntent('succeeded', ['purchase_id' => (string) $purchase->id], 1000);

        $this->actingAs($buyer)
            ->postJson(route('payment.stripe.confirm', $purchase), ['payment_intent_id' => 'pi_test_123'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'completed',
            'payment_id' => 'pi_test_123',
        ]);
    }

    public function test_confirm_rejects_intent_belonging_to_another_purchase(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $promptA = $this->paidPrompt($seller);
        $promptB = $this->paidPrompt($seller);

        $purchaseService = $this->app->make(PurchaseService::class);
        $purchaseA = $purchaseService->initiatePurchase($buyer, $promptA);
        $purchaseB = $purchaseService->initiatePurchase($buyer, $promptB);

        // A cheap succeeded intent bound to purchase A must not complete purchase B (RTE-01).
        $this->mockIntent('succeeded', ['purchase_id' => (string) $purchaseA->id], 100);

        $this->actingAs($buyer)
            ->postJson(route('payment.stripe.confirm', $purchaseB), ['payment_intent_id' => 'pi_other'])
            ->assertStatus(400);

        $this->assertDatabaseHas('purchases', ['id' => $purchaseB->id, 'status' => 'pending']);
    }

    public function test_confirm_rejects_amount_mismatch(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $prompt = $this->paidPrompt($seller);

        $purchase = $this->app->make(PurchaseService::class)
            ->initiatePurchase($buyer, $prompt);

        $this->mockIntent('succeeded', ['purchase_id' => (string) $purchase->id], 100);

        $this->actingAs($buyer)
            ->postJson(route('payment.stripe.confirm', $purchase), ['payment_intent_id' => 'pi_short'])
            ->assertStatus(400);

        $this->assertDatabaseHas('purchases', ['id' => $purchase->id, 'status' => 'pending']);
    }

    public function test_confirm_rejects_purchase_that_is_not_pending(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $prompt = $this->paidPrompt($seller);

        $purchaseService = $this->app->make(PurchaseService::class);
        $purchase = $purchaseService->initiatePurchase($buyer, $prompt);
        $purchaseService->completePurchase($purchase, 'stripe', 'pi_paid');

        $this->mockIntent('succeeded', ['purchase_id' => (string) $purchase->id], 1000);

        $this->actingAs($buyer)
            ->postJson(route('payment.stripe.confirm', $purchase), ['payment_intent_id' => 'pi_paid'])
            ->assertStatus(400);
    }
}
