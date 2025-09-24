<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class StripeService
{
    private $stripeSecretKey;
    private $stripePublishableKey;

    public function __construct()
    {
        $this->stripeSecretKey = config('services.stripe.secret');
        $this->stripePublishableKey = config('services.stripe.key');
    }

    /**
     * Create a payment intent for a prompt purchase.
     */
    public function createPaymentIntent(User $user, Prompt $prompt, float $amount): array
    {
        try {
            // For now, we'll create a mock payment intent
            // This should be replaced with actual Stripe API call once the package is installed
            
            $paymentIntentId = 'pi_' . uniqid();
            $clientSecret = $paymentIntentId . '_secret_' . uniqid();

            // Create a pending payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => 'USD',
                'payment_gateway' => 'stripe',
                'transaction_id' => $paymentIntentId,
                'status' => 'pending',
                'metadata' => [
                    'prompt_id' => $prompt->id,
                    'prompt_title' => $prompt->title,
                    'user_email' => $user->email,
                ],
            ]);

            Log::info('Payment intent created', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'prompt_id' => $prompt->id,
                'amount' => $amount,
                'payment_intent_id' => $paymentIntentId,
            ]);

            return [
                'payment_intent_id' => $paymentIntentId,
                'client_secret' => $clientSecret,
                'amount' => $amount * 100, // Stripe expects amount in cents
                'currency' => 'usd',
                'payment_id' => $payment->id,
            ];

            // TODO: Replace with actual Stripe implementation:
            /*
            \Stripe\Stripe::setApiKey($this->stripeSecretKey);

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => 'usd',
                'metadata' => [
                    'user_id' => $user->id,
                    'prompt_id' => $prompt->id,
                    'prompt_title' => $prompt->title,
                ],
                'description' => "Purchase of prompt: {$prompt->title}",
            ]);

            // Create a pending payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => 'USD',
                'payment_gateway' => 'stripe',
                'transaction_id' => $paymentIntent->id,
                'status' => 'pending',
                'metadata' => [
                    'prompt_id' => $prompt->id,
                    'prompt_title' => $prompt->title,
                    'user_email' => $user->email,
                ],
            ]);

            return [
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'payment_id' => $payment->id,
            ];
            */

        } catch (\Exception $e) {
            Log::error('Failed to create payment intent', [
                'user_id' => $user->id,
                'prompt_id' => $prompt->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to create payment intent: ' . $e->getMessage());
        }
    }

    /**
     * Confirm a payment intent and complete the purchase.
     */
    public function confirmPayment(string $paymentIntentId): array
    {
        try {
            // For now, we'll simulate a successful payment confirmation
            // This should be replaced with actual Stripe API call once the package is installed

            $payment = Payment::where('transaction_id', $paymentIntentId)->first();
            
            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            // Update payment status
            $payment->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            Log::info('Payment confirmed', [
                'payment_id' => $payment->id,
                'payment_intent_id' => $paymentIntentId,
            ]);

            return [
                'status' => 'succeeded',
                'payment_intent_id' => $paymentIntentId,
                'payment_id' => $payment->id,
            ];

            // TODO: Replace with actual Stripe implementation:
            /*
            \Stripe\Stripe::setApiKey($this->stripeSecretKey);

            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            
            if ($paymentIntent->status === 'succeeded') {
                $payment = Payment::where('transaction_id', $paymentIntentId)->first();
                
                if ($payment) {
                    $payment->update([
                        'status' => 'completed',
                        'processed_at' => now(),
                    ]);
                }

                return [
                    'status' => $paymentIntent->status,
                    'payment_intent_id' => $paymentIntent->id,
                    'payment_id' => $payment->id ?? null,
                ];
            }

            throw new \Exception('Payment not successful');
            */

        } catch (\Exception $e) {
            Log::error('Failed to confirm payment', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to confirm payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle Stripe webhook events.
     */
    public function handleWebhook(array $payload, string $signature): bool
    {
        try {
            // For now, we'll simulate webhook handling
            // This should be replaced with actual Stripe webhook verification once the package is installed

            $event = $payload;
            
            Log::info('Stripe webhook received', [
                'event_type' => $event['type'] ?? 'unknown',
                'event_id' => $event['id'] ?? 'unknown',
            ]);

            switch ($event['type'] ?? '') {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSucceeded($event['data']['object'] ?? []);
                    break;
                
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event['data']['object'] ?? []);
                    break;
                
                default:
                    Log::info('Unhandled webhook event type', ['type' => $event['type'] ?? 'unknown']);
            }

            return true;

            // TODO: Replace with actual Stripe implementation:
            /*
            $webhookSecret = config('services.stripe.webhook_secret');
            
            \Stripe\Stripe::setApiKey($this->stripeSecretKey);
            
            $event = \Stripe\Webhook::constructEvent(
                json_encode($payload),
                $signature,
                $webhookSecret
            );

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;
                
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;
                
                default:
                    Log::info('Unhandled webhook event type', ['type' => $event->type]);
            }

            return true;
            */

        } catch (\Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return false;
        }
    }

    /**
     * Handle successful payment webhook.
     */
    private function handlePaymentSucceeded(array $paymentIntent): void
    {
        $paymentIntentId = $paymentIntent['id'] ?? null;
        
        if (!$paymentIntentId) {
            return;
        }

        $payment = Payment::where('transaction_id', $paymentIntentId)->first();
        
        if ($payment && $payment->status !== 'completed') {
            $payment->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            Log::info('Payment marked as completed via webhook', [
                'payment_id' => $payment->id,
                'payment_intent_id' => $paymentIntentId,
            ]);
        }
    }

    /**
     * Handle failed payment webhook.
     */
    private function handlePaymentFailed(array $paymentIntent): void
    {
        $paymentIntentId = $paymentIntent['id'] ?? null;
        
        if (!$paymentIntentId) {
            return;
        }

        $payment = Payment::where('transaction_id', $paymentIntentId)->first();
        
        if ($payment && $payment->status !== 'failed') {
            $payment->update([
                'status' => 'failed',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'failure_reason' => $paymentIntent['last_payment_error']['message'] ?? 'Unknown error',
                    'failed_at' => now()->toISOString(),
                ]),
            ]);

            Log::info('Payment marked as failed via webhook', [
                'payment_id' => $payment->id,
                'payment_intent_id' => $paymentIntentId,
            ]);
        }
    }

    /**
     * Get Stripe publishable key for frontend.
     */
    public function getPublishableKey(): string
    {
        return $this->stripePublishableKey ?? 'pk_test_mock_key';
    }
}
