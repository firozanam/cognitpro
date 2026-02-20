<?php

namespace App\Modules\Prompt\Repositories;

use App\Modules\Prompt\Contracts\ReviewRepositoryInterface;
use App\Modules\Prompt\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * Find a review by ID.
     */
    public function find(int $id): ?Review
    {
        return Review::with(['user', 'prompt', 'prompt.seller', 'purchase'])->find($id);
    }

    /**
     * Get reviews for a prompt.
     */
    public function getByPromptId(int $promptId, int $perPage = 10): LengthAwarePaginator
    {
        return Review::with(['user'])
            ->where('prompt_id', $promptId)
            ->orderBy('helpful_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get reviews by user.
     */
    public function getByUserId(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Review::with(['prompt', 'prompt.seller'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get review for a purchase.
     */
    public function getByPurchaseId(int $purchaseId): ?Review
    {
        return Review::where('purchase_id', $purchaseId)->first();
    }

    /**
     * Create a new review.
     */
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    /**
     * Update a review.
     */
    public function update(Review $review, array $data): bool
    {
        return $review->update($data);
    }

    /**
     * Delete a review.
     */
    public function delete(Review $review): bool
    {
        return $review->delete();
    }

    /**
     * Check if a purchase has been reviewed.
     */
    public function hasReviewed(int $purchaseId): bool
    {
        return Review::where('purchase_id', $purchaseId)->exists();
    }

    /**
     * Get recent reviews.
     */
    public function getRecent(int $limit = 10): Collection
    {
        return Review::with(['user', 'prompt'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get average rating for a prompt.
     */
    public function getAverageRatingForPrompt(int $promptId): float
    {
        return (float) Review::where('prompt_id', $promptId)
            ->avg('rating') ?? 0;
    }

    /**
     * Get rating distribution for a prompt.
     */
    public function getRatingDistributionForPrompt(int $promptId): array
    {
        $distribution = Review::where('prompt_id', $promptId)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Ensure all ratings 1-5 are present
        $result = [];
        for ($i = 1; $i <= 5; $i++) {
            $result[$i] = $distribution[$i] ?? 0;
        }

        return $result;
    }
}
