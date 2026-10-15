<?php

namespace App\Modules\Prompt\Services;

use App\Modules\Prompt\Contracts\PurchaseRepositoryInterface;
use App\Modules\Prompt\Contracts\PromptRepositoryInterface;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use App\Notifications\PurchaseConfirmationNotification;
use App\Notifications\SellerSaleNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PurchaseService
{
    protected PurchaseRepositoryInterface $purchaseRepository;
    protected PromptRepositoryInterface $promptRepository;

    /**
     * Platform fee percentage.
     */
    protected const PLATFORM_FEE_PERCENTAGE = 15;

    public function __construct(
        PurchaseRepositoryInterface $purchaseRepository,
        PromptRepositoryInterface $promptRepository
    ) {
        $this->purchaseRepository = $purchaseRepository;
        $this->promptRepository = $promptRepository;
    }

    /**
     * Get purchases for a buyer.
     */
    public function getBuyerPurchases(int $buyerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->purchaseRepository->getByBuyerId($buyerId, $perPage);
    }

    /**
     * Get sales for a seller.
     */
    public function getSellerSales(int $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->purchaseRepository->getBySellerId($sellerId, $perPage);
    }

    /**
     * Get a purchase by ID.
     */
    public function getPurchase(int $id): ?Purchase
    {
        return $this->purchaseRepository->find($id);
    }

    /**
     * Get a purchase by order number.
     */
    public function getPurchaseByOrderNumber(string $orderNumber): ?Purchase
    {
        return $this->purchaseRepository->findByOrderNumber($orderNumber);
    }

    /**
     * Check if user has purchased a prompt.
     */
    public function hasPurchased(int $buyerId, int $promptId): bool
    {
        return $this->purchaseRepository->hasPurchased($buyerId, $promptId);
    }

    /**
     * Initiate a purchase for a prompt.
     */
    public function initiatePurchase(User $buyer, Prompt $prompt, ?float $customPrice = null): Purchase
    {
        // Validate buyer can purchase
        if ($buyer->id === $prompt->seller_id) {
            throw new Exception('You cannot purchase your own prompt.');
        }

        // Check if already purchased
        if ($this->hasPurchased($buyer->id, $prompt->id)) {
            throw new Exception('You have already purchased this prompt.');
        }

        // Validate prompt is available
        if (!$prompt->isApproved()) {
            throw new Exception('This prompt is not available for purchase.');
        }

        // Calculate price
        $price = $this->calculatePrice($prompt, $customPrice);

        // Calculate fees
        $platformFee = round($price * (self::PLATFORM_FEE_PERCENTAGE / 100), 2);
        $sellerEarnings = round($price - $platformFee, 2);

        // Reuse an existing row for this buyer+prompt (DB-02): the pair is
        // unique in the database, so retries reuse instead of stacking.
        $existing = Purchase::query()
            ->where('buyer_id', $buyer->id)
            ->where('prompt_id', $prompt->id)
            ->first();

        if ($existing) {
            return $this->resetForRetry($existing, $price, $platformFee, $sellerEarnings);
        }

        try {
            return $this->purchaseRepository->create([
                'buyer_id' => $buyer->id,
                'prompt_id' => $prompt->id,
                'price' => $price,
                'platform_fee' => $platformFee,
                'seller_earnings' => $sellerEarnings,
                'status' => 'pending',
                'payment_method' => null,
                'payment_id' => null,
                'purchased_at' => null,
            ]);
        } catch (UniqueConstraintViolationException) {
            // A concurrent request won the race — reuse its row.
            $winner = Purchase::query()
                ->where('buyer_id', $buyer->id)
                ->where('prompt_id', $prompt->id)
                ->firstOrFail();

            return $this->resetForRetry($winner, $price, $platformFee, $sellerEarnings);
        }
    }

    /**
     * Reset a non-completed purchase row for a new attempt (DB-02 reuse path).
     */
    protected function resetForRetry(Purchase $purchase, float $price, float $platformFee, float $sellerEarnings): Purchase
    {
        if (!$purchase->isPending()) {
            $this->purchaseRepository->update($purchase, [
                'price' => $price,
                'platform_fee' => $platformFee,
                'seller_earnings' => $sellerEarnings,
                'status' => 'pending',
                'payment_method' => null,
                'payment_id' => null,
                'purchased_at' => null,
                'refunded_at' => null,
            ]);
        }

        return $purchase->refresh();
    }

    /**
     * Complete a purchase after payment.
     *
     * Idempotent (DB-04): the row is locked and its status re-checked, so
     * concurrent webhook + confirm calls (or replays) complete it exactly once.
     */
    public function completePurchase(Purchase $purchase, string $paymentMethod, string $paymentId): Purchase
    {
        DB::beginTransaction();
        try {
            $fresh = Purchase::query()->whereKey($purchase->getKey())->lockForUpdate()->firstOrFail();

            // Already processed by a concurrent confirm or webhook — no counters, no duplicate notifications.
            if ($fresh->status !== 'pending') {
                DB::rollBack();

                return $fresh;
            }

            // Update purchase status
            $this->purchaseRepository->update($fresh, [
                'status' => 'completed',
                'payment_method' => $paymentMethod,
                'payment_id' => $paymentId,
                'purchased_at' => now(),
            ]);

            // Increment prompt purchase count
            $prompt = $fresh->prompt;
            $prompt->incrementPurchaseCount();

            // Update seller stats
            $seller = $prompt->seller;
            if ($seller && $seller->profile) {
                $seller->profile->incrementSales();
                $seller->profile->addEarnings($fresh->seller_earnings);
            }

            DB::commit();

            // Refresh the purchase
            $fresh->refresh();

            // Send notifications
            $this->sendPurchaseNotifications($fresh);

            Log::info('Purchase completed', [
                'purchase_id' => $fresh->id,
                'order_number' => $fresh->order_number,
                'buyer_id' => $fresh->buyer_id,
                'prompt_id' => $fresh->prompt_id,
                'price' => $fresh->price,
            ]);

            return $fresh;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete purchase', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Fail a purchase.
     */
    public function failPurchase(Purchase $purchase, string $reason = null): Purchase
    {
        $this->purchaseRepository->update($purchase, [
            'status' => 'failed',
        ]);

        Log::info('Purchase failed', [
            'purchase_id' => $purchase->id,
            'order_number' => $purchase->order_number,
            'reason' => $reason,
        ]);

        return $purchase->refresh();
    }

    /**
     * Refund a purchase.
     */
    public function refundPurchase(Purchase $purchase, string $reason = null): Purchase
    {
        DB::beginTransaction();
        try {
            // Update purchase status
            $this->purchaseRepository->update($purchase, [
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            // Decrement prompt purchase count
            $prompt = $purchase->prompt;
            $prompt->decrement('purchases_count');

            // Update seller stats
            $seller = $prompt->seller;
            if ($seller && $seller->profile) {
                $seller->profile->decrement('total_sales');
                $seller->profile->decrement('total_earnings', $purchase->seller_earnings);
            }

            DB::commit();

            Log::info('Purchase refunded', [
                'purchase_id' => $purchase->id,
                'order_number' => $purchase->order_number,
                'reason' => $reason,
            ]);

            return $purchase->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to refund purchase', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get purchase statistics for a seller.
     */
    public function getSellerStats(int $sellerId): array
    {
        $salesCount = $this->purchaseRepository->getSalesCountForSeller($sellerId);
        $earnings = $this->purchaseRepository->getEarningsForSeller($sellerId);

        return [
            'total_sales' => $salesCount,
            'total_earnings' => $earnings,
            'average_order_value' => $salesCount > 0 ? round($earnings / $salesCount, 2) : 0,
        ];
    }

    /**
     * Get recent purchases for admin dashboard.
     */
    public function getRecentPurchases(int $limit = 10): Collection
    {
        return $this->purchaseRepository->getRecent($limit);
    }

    /**
     * Get total platform revenue.
     */
    public function getTotalRevenue(): float
    {
        return $this->purchaseRepository->getTotalRevenue();
    }

    /**
     * Get revenue for a specific period.
     */
    public function getRevenueForPeriod(\DateTime $startDate, \DateTime $endDate): float
    {
        return $this->purchaseRepository->getRevenueForPeriod($startDate, $endDate);
    }

    /**
     * Calculate the price for a purchase.
     */
    protected function calculatePrice(Prompt $prompt, ?float $customPrice = null): float
    {
        // Free prompts
        if ($prompt->isFree()) {
            return 0;
        }

        // Pay what you want
        if ($prompt->isPayWhatYouWant()) {
            if ($customPrice === null) {
                throw new Exception('A price must be specified for pay-what-you-want prompts.');
            }
            if ($prompt->min_price && $customPrice < $prompt->min_price) {
                throw new Exception("Minimum price is \${$prompt->min_price}.");
            }
            return round($customPrice, 2);
        }

        // Fixed price
        return $prompt->price;
    }

    /**
     * Get total sales count.
     */
    public function getTotalSales(): int
    {
        return Purchase::where('status', 'completed')->count();
    }

    /**
     * Send purchase notifications to buyer and seller.
     */
    protected function sendPurchaseNotifications(Purchase $purchase): void
    {
        $prompt = $purchase->prompt;
        $buyer = $purchase->buyer;
        $seller = $prompt->seller ?? null;

        // Send confirmation to buyer
        if ($buyer) {
            $buyer->notify(new PurchaseConfirmationNotification([
                'purchase_id' => $purchase->id,
                'order_number' => $purchase->order_number,
                'prompt_title' => $prompt->title,
                'price' => $purchase->price,
            ]));
        }

        // Notify seller of the sale
        if ($seller && $seller->id !== $buyer->id) {
            $seller->notify(new SellerSaleNotification([
                'purchase_id' => $purchase->id,
                'prompt_title' => $prompt->title,
                'price' => $purchase->price,
                'seller_earnings' => $purchase->seller_earnings,
                'order_number' => $purchase->order_number,
            ]));
        }
    }
}
