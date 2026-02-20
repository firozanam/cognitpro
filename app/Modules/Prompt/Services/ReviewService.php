<?php

namespace App\Modules\Prompt\Services;

use App\Modules\Prompt\Contracts\ReviewRepositoryInterface;
use App\Modules\Prompt\Contracts\PurchaseRepositoryInterface;
use App\Modules\Prompt\Models\Review;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReviewService
{
    protected ReviewRepositoryInterface $reviewRepository;
    protected PurchaseRepositoryInterface $purchaseRepository;

    public function __construct(
        ReviewRepositoryInterface $reviewRepository,
        PurchaseRepositoryInterface $purchaseRepository
    ) {
        $this->reviewRepository = $reviewRepository;
        $this->purchaseRepository = $purchaseRepository;
    }

    /**
     * Get reviews for a prompt.
     */
    public function getPromptReviews(int $promptId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviewRepository->getByPromptId($promptId, $perPage);
    }

    /**
     * Get reviews by a user.
     */
    public function getUserReviews(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviewRepository->getByUserId($userId, $perPage);
    }

    /**
     * Get a review by ID.
     */
    public function getReview(int $id): ?Review
    {
        return $this->reviewRepository->find($id);
    }

    /**
     * Create a review for a purchase.
     */
    public function createReview(User $user, Purchase $purchase, array $data): Review
    {
        // Verify the user owns this purchase
        if ($purchase->buyer_id !== $user->id) {
            throw new Exception('You can only review your own purchases.');
        }

        // Verify the purchase is completed
        if (!$purchase->isCompleted()) {
            throw new Exception('You can only review completed purchases.');
        }

        // Check if already reviewed
        if ($this->reviewRepository->hasReviewed($purchase->id)) {
            throw new Exception('You have already reviewed this purchase.');
        }

        DB::beginTransaction();
        try {
            $review = $this->reviewRepository->create([
                'prompt_id' => $purchase->prompt_id,
                'user_id' => $user->id,
                'purchase_id' => $purchase->id,
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'content' => $data['content'] ?? null,
                'helpful_count' => 0,
            ]);

            // Update prompt rating
            $prompt = $purchase->prompt;
            $prompt->updateRating($data['rating']);

            DB::commit();

            Log::info('Review created', [
                'review_id' => $review->id,
                'user_id' => $user->id,
                'prompt_id' => $purchase->prompt_id,
                'rating' => $data['rating'],
            ]);

            return $review;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create review', [
                'user_id' => $user->id,
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update a review.
     */
    public function updateReview(Review $review, User $user, array $data): Review
    {
        // Verify ownership
        if ($review->user_id !== $user->id) {
            throw new Exception('You can only edit your own reviews.');
        }

        $oldRating = $review->rating;

        DB::beginTransaction();
        try {
            $this->reviewRepository->update($review, [
                'rating' => $data['rating'] ?? $review->rating,
                'title' => $data['title'] ?? $review->title,
                'content' => $data['content'] ?? $review->content,
            ]);

            // Update prompt rating if rating changed
            if (isset($data['rating']) && $data['rating'] !== $oldRating) {
                $prompt = $review->prompt;
                $this->recalculatePromptRating($prompt);
            }

            DB::commit();

            return $review->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a review.
     */
    public function deleteReview(Review $review, User $user): bool
    {
        // Verify ownership
        if ($review->user_id !== $user->id && !$user->isAdmin()) {
            throw new Exception('You can only delete your own reviews.');
        }

        DB::beginTransaction();
        try {
            $prompt = $review->prompt;
            
            $deleted = $this->reviewRepository->delete($review);

            // Recalculate prompt rating
            $this->recalculatePromptRating($prompt);

            DB::commit();

            Log::info('Review deleted', [
                'review_id' => $review->id,
                'user_id' => $user->id,
            ]);

            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete review', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Add seller response to a review.
     */
    public function addSellerResponse(Review $review, User $seller, string $response): Review
    {
        // Verify the user is the seller of the prompt
        if ($review->prompt->seller_id !== $seller->id) {
            throw new Exception('Only the prompt seller can respond to reviews.');
        }

        // Check if already responded
        if ($review->hasSellerResponse()) {
            throw new Exception('You have already responded to this review.');
        }

        $review->addSellerResponse($response);

        Log::info('Seller response added', [
            'review_id' => $review->id,
            'seller_id' => $seller->id,
        ]);

        return $review->refresh();
    }

    /**
     * Mark a review as helpful.
     */
    public function markAsHelpful(Review $review): Review
    {
        $review->incrementHelpfulCount();
        return $review->refresh();
    }

    /**
     * Get recent reviews.
     */
    public function getRecentReviews(int $limit = 10): Collection
    {
        return $this->reviewRepository->getRecent($limit);
    }

    /**
     * Get rating statistics for a prompt.
     */
    public function getPromptRatingStats(int $promptId): array
    {
        return [
            'average' => $this->reviewRepository->getAverageRatingForPrompt($promptId),
            'distribution' => $this->reviewRepository->getRatingDistributionForPrompt($promptId),
        ];
    }

    /**
     * Recalculate prompt rating from all reviews.
     */
    protected function recalculatePromptRating($prompt): void
    {
        $stats = $prompt->reviews()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        $prompt->update([
            'rating' => round($stats->avg_rating ?? 0, 2),
            'rating_count' => $stats->count ?? 0,
        ]);
    }
}
