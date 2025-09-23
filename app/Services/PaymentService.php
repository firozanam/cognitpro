<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Process a payment for a prompt purchase.
     */
    public function processPayment(User $user, Prompt $prompt, float $amount, string $gateway, string $transactionId): Purchase
    {
        return DB::transaction(function () use ($user, $prompt, $amount, $gateway, $transactionId) {
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => 'USD',
                'payment_gateway' => $gateway,
                'transaction_id' => $transactionId,
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // Create purchase record
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'prompt_id' => $prompt->id,
                'price_paid' => $amount,
                'payment_gateway' => $gateway,
                'transaction_id' => $transactionId,
                'purchased_at' => now(),
            ]);

            // Log the transaction
            Log::info('Payment processed successfully', [
                'user_id' => $user->id,
                'prompt_id' => $prompt->id,
                'amount' => $amount,
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
            ]);

            return $purchase;
        });
    }

    /**
     * Calculate platform commission.
     */
    public function calculateCommission(float $amount): float
    {
        // Default 10% commission
        $commissionRate = config('app.commission_rate', 0.10);
        return $amount * $commissionRate;
    }

    /**
     * Calculate seller payout amount.
     */
    public function calculateSellerPayout(float $amount): float
    {
        $commission = $this->calculateCommission($amount);
        return $amount - $commission;
    }

    /**
     * Validate payment amount for a prompt.
     */
    public function validatePaymentAmount(Prompt $prompt, float $amount): bool
    {
        if ($prompt->price_type === 'free') {
            return $amount == 0;
        }

        if ($prompt->price_type === 'fixed') {
            return abs($amount - $prompt->price) < 0.01;
        }

        if ($prompt->price_type === 'pay_what_you_want') {
            return $amount >= $prompt->minimum_price;
        }

        return false;
    }

    /**
     * Process refund for a purchase.
     */
    public function processRefund(Purchase $purchase, string $reason = null): bool
    {
        try {
            return DB::transaction(function () use ($purchase, $reason) {
                // Update payment status
                $payment = Payment::where('transaction_id', $purchase->transaction_id)
                    ->where('user_id', $purchase->user_id)
                    ->first();

                if ($payment) {
                    $payment->update([
                        'status' => 'refunded',
                        'metadata' => array_merge($payment->metadata ?? [], [
                            'refund_reason' => $reason,
                            'refunded_at' => now()->toISOString(),
                        ]),
                    ]);
                }

                // Log the refund
                Log::info('Refund processed', [
                    'purchase_id' => $purchase->id,
                    'user_id' => $purchase->user_id,
                    'prompt_id' => $purchase->prompt_id,
                    'amount' => $purchase->price_paid,
                    'reason' => $reason,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
