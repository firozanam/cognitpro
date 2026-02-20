<?php

namespace App\Modules\Prompt\Contracts;

use App\Modules\Prompt\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ReviewRepositoryInterface
{
    /**
     * Find a review by ID.
     */
    public function find(int $id): ?Review;

    /**
     * Get reviews for a prompt.
     */
    public function getByPromptId(int $promptId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get reviews by user.
     */
    public function getByUserId(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get review for a purchase.
     */
    public function getByPurchaseId(int $purchaseId): ?Review;

    /**
     * Create a new review.
     */
    public function create(array $data): Review;

    /**
     * Update a review.
     */
    public function update(Review $review, array $data): bool;

    /**
     * Delete a review.
     */
    public function delete(Review $review): bool;

    /**
     * Check if a purchase has been reviewed.
     */
    public function hasReviewed(int $purchaseId): bool;

    /**
     * Get recent reviews.
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Get average rating for a prompt.
     */
    public function getAverageRatingForPrompt(int $promptId): float;

    /**
     * Get rating distribution for a prompt.
     */
    public function getRatingDistributionForPrompt(int $promptId): array;
}
