<?php

namespace Tests\Feature\Payment;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\Prompt\Services\PurchaseService;
use App\Modules\User\Models\User;
use App\Notifications\PurchaseConfirmationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected string $webhookSecret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.webhook_secret' => $this->webhookSecret]);
    }

    /**
     * Build a Stripe-Signature header the way stripe-php does, so the
     * controller exercises the real signature verification path.
     */
    protected function signedEvent(array $event): array
    {
        $payload = json_encode($event);
        $timestamp = time();

        $signature = sprintf('t=%d,v1=%s', $timestamp, hash_hmac('sha256', "{$timestamp}.{$payload}", $this->webhookSecret));

        return [$payload, $signature];
    }

    protected function postWebhook(string $payload, string $signature): TestResponse
    {
        return $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => $signature,
        ], $payload);
    }

    protected function pendingPurchase(): Purchase
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'fixed',
            'price' => 10.00,
            'status' => 'approved',
            'purchases_count' => 0,
        ]);

        return $this->app->make(PurchaseService::class)->initiatePurchase($buyer, $prompt);
    }

    public function test_succeeded_webhook_completes_purchase(): void
    {
        Notification::fake();

        $purchase = $this->pendingPurchase();

        [$payload, $signature] = $this->signedEvent([
            'id' => 'evt_test_1',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => [
                'id' => 'pi_test_1',
                'amount' => 1000,
                'metadata' => ['purchase_id' => (string) $purchase->id],
            ]],
        ]);

        $this->postWebhook($payload, $signature)->assertOk();

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'completed',
            'payment_id' => 'pi_test_1',
        ]);

        Notification::assertSentTo($purchase->buyer, PurchaseConfirmationNotification::class);
    }

    public function test_failed_webhook_marks_purchase_failed(): void
    {
        $purchase = $this->pendingPurchase();

        [$payload, $signature] = $this->signedEvent([
            'id' => 'evt_test_2',
            'type' => 'payment_intent.payment_failed',
            'data' => ['object' => [
                'id' => 'pi_test_2',
                'metadata' => ['purchase_id' => (string) $purchase->id],
                'last_payment_error' => ['message' => 'Your card was declined.'],
            ]],
        ]);

        $this->postWebhook($payload, $signature)->assertOk();

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'failed',
        ]);
    }

    public function test_succeeded_webhook_is_idempotent(): void
    {
        Notification::fake();

        $purchase = $this->pendingPurchase();

        $event = [
            'id' => 'evt_test_3',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => [
                'id' => 'pi_test_3',
                'amount' => 1000,
                'metadata' => ['purchase_id' => (string) $purchase->id],
            ]],
        ];

        [$payload, $signature] = $this->signedEvent($event);

        $this->postWebhook($payload, $signature)->assertOk();
        $this->postWebhook($payload, $signature)->assertOk();

        $this->assertSame(1, (int) $purchase->prompt->fresh()->purchases_count);
        Notification::assertSentToTimes($purchase->buyer, PurchaseConfirmationNotification::class, 1);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $purchase = $this->pendingPurchase();

        [$payload] = $this->signedEvent([
            'id' => 'evt_bad',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => [
                'id' => 'pi_bad',
                'metadata' => ['purchase_id' => (string) $purchase->id],
            ]],
        ]);

        $this->postWebhook($payload, 't=1,v1=deadbeef')->assertStatus(400);

        $this->assertDatabaseHas('purchases', ['id' => $purchase->id, 'status' => 'pending']);
    }

    public function test_succeeded_webhook_completes_a_failed_purchase_retry(): void
    {
        Notification::fake();

        $purchase = $this->pendingPurchase();
        $this->app->make(PurchaseService::class)->failPurchase($purchase, 'card_declined');

        [$payload, $signature] = $this->signedEvent([
            'id' => 'evt_test_4',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => [
                'id' => 'pi_test_4',
                'amount' => 1000,
                'metadata' => ['purchase_id' => (string) $purchase->id],
            ]],
        ]);

        $this->postWebhook($payload, $signature)->assertOk();

        // The retried payment captured funds — the purchase must complete.
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'completed',
            'payment_id' => 'pi_test_4',
        ]);
    }
}
