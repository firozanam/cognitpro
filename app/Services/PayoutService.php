<?php

namespace App\Services;

use App\Models\Payout;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayoutService
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Calculate pending earnings for a seller.
     */
    public function calculatePendingEarnings(User $seller): float
    {
        // Get all purchases for seller's prompts that haven't been paid out yet
        $purchases = Purchase::whereHas('prompt', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })
        ->whereDoesntHave('payout')
        ->get();

        $totalEarnings = 0;

        foreach ($purchases as $purchase) {
            $sellerAmount = $this->paymentService->calculateSellerPayout($purchase->price_paid);
            $totalEarnings += $sellerAmount;
        }

        return $totalEarnings;
    }

    /**
     * Create a payout for a seller.
     */
    public function createPayout(User $seller, float $amount, \DateTime $scheduledFor = null): Payout
    {
        if (!$scheduledFor) {
            $scheduledFor = now()->addDays(7); // Default 7 days from now
        }

        return Payout::create([
            'user_id' => $seller->id,
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'pending',
            'scheduled_for' => $scheduledFor,
        ]);
    }

    /**
     * Process scheduled payouts.
     */
    public function processScheduledPayouts(): int
    {
        $payouts = Payout::where('status', 'pending')
            ->where('scheduled_for', '<=', now())
            ->get();

        $processedCount = 0;

        foreach ($payouts as $payout) {
            if ($this->processPayout($payout)) {
                $processedCount++;
            }
        }

        return $processedCount;
    }

    /**
     * Process a single payout.
     */
    public function processPayout(Payout $payout): bool
    {
        try {
            return DB::transaction(function () use ($payout) {
                // Here you would integrate with Payoneer API
                // For now, we'll simulate the process
                
                $transactionId = 'PO_' . time() . '_' . $payout->id;
                
                $payout->update([
                    'status' => 'processed',
                    'payoneer_transaction_id' => $transactionId,
                    'processed_at' => now(),
                    'metadata' => [
                        'processed_by' => 'system',
                        'processing_method' => 'payoneer_api',
                    ],
                ]);

                Log::info('Payout processed successfully', [
                    'payout_id' => $payout->id,
                    'user_id' => $payout->user_id,
                    'amount' => $payout->amount,
                    'transaction_id' => $transactionId,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Payout processing failed', [
                'payout_id' => $payout->id,
                'error' => $e->getMessage(),
            ]);

            $payout->update([
                'status' => 'failed',
                'metadata' => array_merge($payout->metadata ?? [], [
                    'error' => $e->getMessage(),
                    'failed_at' => now()->toISOString(),
                ]),
            ]);

            return false;
        }
    }

    /**
     * Get payout history for a seller.
     */
    public function getPayoutHistory(User $seller): Collection
    {
        return Payout::where('user_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Calculate total earnings for a seller.
     */
    public function calculateTotalEarnings(User $seller): array
    {
        $purchases = Purchase::whereHas('prompt', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })->get();

        $totalRevenue = $purchases->sum('price_paid');
        $totalEarnings = 0;

        foreach ($purchases as $purchase) {
            $totalEarnings += $this->paymentService->calculateSellerPayout($purchase->price_paid);
        }

        $paidOut = Payout::where('user_id', $seller->id)
            ->where('status', 'processed')
            ->sum('amount');

        return [
            'total_revenue' => $totalRevenue,
            'total_earnings' => $totalEarnings,
            'paid_out' => $paidOut,
            'pending' => $totalEarnings - $paidOut,
        ];
    }
}
