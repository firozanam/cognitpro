<?php

namespace App\Modules\Prompt\Contracts;

use App\Modules\Prompt\Models\Purchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseRepositoryInterface
{
    /**
     * Find a purchase by ID.
     */
    public function find(int $id): ?Purchase;

    /**
     * Find a purchase by order number.
     */
    public function findByOrderNumber(string $orderNumber): ?Purchase;

    /**
     * Get purchases by buyer ID.
     */
    public function getByBuyerId(int $buyerId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get purchases by prompt ID.
     */
    public function getByPromptId(int $promptId): Collection;

    /**
     * Get purchases by seller (through prompts).
     */
    public function getBySellerId(int $sellerId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if user has purchased a prompt.
     */
    public function hasPurchased(int $buyerId, int $promptId): bool;

    /**
     * Create a new purchase.
     */
    public function create(array $data): Purchase;

    /**
     * Update a purchase.
     */
    public function update(Purchase $purchase, array $data): bool;

    /**
     * Update purchase status.
     */
    public function updateStatus(Purchase $purchase, string $status): bool;

    /**
     * Get recent purchases.
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Get total revenue.
     */
    public function getTotalRevenue(): float;

    /**
     * Get revenue for a specific period.
     */
    public function getRevenueForPeriod(\DateTime $startDate, \DateTime $endDate): float;

    /**
     * Get sales count for a seller.
     */
    public function getSalesCountForSeller(int $sellerId): int;

    /**
     * Get earnings for a seller.
     */
    public function getEarningsForSeller(int $sellerId): float;
}
