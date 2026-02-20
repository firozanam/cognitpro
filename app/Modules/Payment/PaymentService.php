<?php

namespace App\Modules\Payment;

use App\Modules\Prompt\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentService
{
    protected string $secretKey;
    protected string $publishableKey;
    protected string $currency;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret');
        $this->publishableKey = config('services.stripe.key');
        $this->currency = config('cashier.currency', 'usd');

        if ($this->secretKey) {
            Stripe::setApiKey($this->secretKey);
        }
    }

    /**
     * Check if Stripe is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->secretKey) && !empty($this->publishableKey);
    }

    /**
     * Get the publishable key for frontend.
     */
    public function getPublishableKey(): ?string
    {
        return $this->publishableKey;
    }

    /**
     * Create a payment intent for a purchase.
     */
    public function createPaymentIntent(Purchase $purchase): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Payment processing is not configured.');
        }

        try {
            $amount = (int) ($purchase->price * 100); // Convert to cents

            $intent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => $this->currency,
                'metadata' => [
                    'purchase_id' => $purchase->id,
                    'order_number' => $purchase->order_number,
                    'buyer_id' => $purchase->buyer_id,
                    'prompt_id' => $purchase->prompt_id,
                ],
                'description' => "Purchase: {$purchase->prompt->title}",
            ]);

            Log::info('Payment intent created', [
                'purchase_id' => $purchase->id,
                'intent_id' => $intent->id,
                'amount' => $amount,
            ]);

            return [
                'client_secret' => $intent->client_secret,
                'intent_id' => $intent->id,
                'amount' => $amount,
                'currency' => $this->currency,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Failed to create payment intent', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to initialize payment. Please try again.');
        }
    }

    /**
     * Confirm a payment intent.
     */
    public function confirmPaymentIntent(string $intentId): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Payment processing is not configured.');
        }

        try {
            $intent = PaymentIntent::retrieve($intentId);

            return [
                'status' => $intent->status,
                'amount' => $intent->amount,
                'currency' => $intent->currency,
                'metadata' => $intent->metadata->toArray(),
            ];
        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve payment intent', [
                'intent_id' => $intentId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to verify payment. Please contact support.');
        }
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): ?array
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $secret
            );

            return [
                'type' => $event->type,
                'data' => $event->data->toArray(),
            ];
        } catch (\UnexpectedValueException $e) {
            Log::warning('Invalid webhook payload', ['error' => $e->getMessage()]);
            return null;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Invalid webhook signature', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Process a refund.
     */
    public function processRefund(Purchase $purchase, ?int $amount = null): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Payment processing is not configured.');
        }

        if (!$purchase->payment_id) {
            throw new \Exception('No payment found for this purchase.');
        }

        try {
            $refundAmount = $amount ?? (int) ($purchase->price * 100);

            $refund = \Stripe\Refund::create([
                'payment_intent' => $purchase->payment_id,
                'amount' => $refundAmount,
                'metadata' => [
                    'purchase_id' => $purchase->id,
                    'order_number' => $purchase->order_number,
                    'reason' => 'Customer requested refund',
                ],
            ]);

            Log::info('Refund processed', [
                'purchase_id' => $purchase->id,
                'refund_id' => $refund->id,
                'amount' => $refundAmount,
            ]);

            return [
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Failed to process refund', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to process refund. Please contact support.');
        }
    }
}
