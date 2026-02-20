<?php

namespace App\Modules\Prompt\Repositories;

use App\Modules\Prompt\Contracts\PurchaseRepositoryInterface;
use App\Modules\Prompt\Models\Purchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseRepository implements PurchaseRepositoryInterface
{
    /**
     * Find a purchase by ID.
     */
    public function find(int $id): ?Purchase
    {
        return Purchase::with(['buyer', 'prompt', 'prompt.seller', 'review'])->find($id);
    }

    /**
     * Find a purchase by order number.
     */
    public function findByOrderNumber(string $orderNumber): ?Purchase
    {
        return Purchase::with(['buyer', 'prompt', 'prompt.seller', 'review'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    /**
     * Get purchases by buyer ID.
     */
    public function getByBuyerId(int $buyerId, int $perPage = 15): LengthAwarePaginator
    {
        return Purchase::with(['prompt', 'prompt.seller', 'prompt.category', 'review'])
            ->where('buyer_id', $buyerId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get purchases by prompt ID.
     */
    public function getByPromptId(int $promptId): Collection
    {
        return Purchase::with(['buyer'])
            ->where('prompt_id', $promptId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get purchases by seller (through prompts).
     */
    public function getBySellerId(int $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return Purchase::with(['buyer', 'prompt'])
            ->whereHas('prompt', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Check if user has purchased a prompt.
     */
    public function hasPurchased(int $buyerId, int $promptId): bool
    {
        return Purchase::where('buyer_id', $buyerId)
            ->where('prompt_id', $promptId)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Create a new purchase.
     */
    public function create(array $data): Purchase
    {
        return Purchase::create($data);
    }

    /**
     * Update a purchase.
     */
    public function update(Purchase $purchase, array $data): bool
    {
        return $purchase->update($data);
    }

    /**
     * Update purchase status.
     */
    public function updateStatus(Purchase $purchase, string $status): bool
    {
        return $purchase->update(['status' => $status]);
    }

    /**
     * Get recent purchases.
     */
    public function getRecent(int $limit = 10): Collection
    {
        return Purchase::with(['buyer', 'prompt'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get total revenue.
     */
    public function getTotalRevenue(): float
    {
        return (float) Purchase::where('status', 'completed')
            ->sum('price');
    }

    /**
     * Get revenue for a specific period.
     */
    public function getRevenueForPeriod(\DateTime $startDate, \DateTime $endDate): float
    {
        return (float) Purchase::where('status', 'completed')
            ->whereBetween('purchased_at', [$startDate, $endDate])
            ->sum('price');
    }

    /**
     * Get sales count for a seller.
     */
    public function getSalesCountForSeller(int $sellerId): int
    {
        return Purchase::where('status', 'completed')
            ->whereHas('prompt', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->count();
    }

    /**
     * Get earnings for a seller.
     */
    public function getEarningsForSeller(int $sellerId): float
    {
        return (float) Purchase::where('status', 'completed')
            ->whereHas('prompt', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->sum('seller_earnings');
    }
}
