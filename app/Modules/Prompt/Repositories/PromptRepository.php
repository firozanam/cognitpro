<?php

namespace App\Modules\Prompt\Repositories;

use App\Modules\Prompt\Contracts\PromptRepositoryInterface;
use App\Modules\Prompt\Models\Prompt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PromptRepository implements PromptRepositoryInterface
{
    /**
     * Get all prompts with pagination.
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Prompt::query()
            ->with(['category', 'tags', 'seller.profile'])
            ->approved();

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (!empty($filters['ai_model'])) {
            $query->byModel($filters['ai_model']);
        }

        if (!empty($filters['pricing_model'])) {
            $query->where('pricing_model', $filters['pricing_model']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        match ($sortBy) {
            'popular' => $query->popular(),
            'rating' => $query->topRated(),
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            default => $query->orderBy($sortBy, $sortOrder),
        };

        return $query->paginate($perPage);
    }

    /**
     * Get prompt by ID.
     */
    public function findById(int $id): ?Prompt
    {
        return Prompt::with(['category', 'tags', 'seller.profile', 'reviews.user'])
            ->find($id);
    }

    /**
     * Get prompt by slug.
     */
    public function findBySlug(string $slug): ?Prompt
    {
        return Prompt::with(['category', 'tags', 'seller.profile', 'reviews.user'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get prompts by seller ID.
     */
    public function getBySellerId(int $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return Prompt::with(['category', 'tags'])
            ->bySeller($sellerId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get prompts by category ID.
     */
    public function getByCategoryId(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return Prompt::with(['category', 'tags', 'seller.profile'])
            ->approved()
            ->byCategory($categoryId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get featured prompts.
     */
    public function getFeatured(int $limit = 10): Collection
    {
        return Prompt::with(['category', 'tags', 'seller.profile'])
            ->approved()
            ->featured()
            ->orderBy('purchases_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get prompts by AI model.
     */
    public function getByAiModel(string $model, int $perPage = 15): LengthAwarePaginator
    {
        return Prompt::with(['category', 'tags', 'seller.profile'])
            ->approved()
            ->byModel($model)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search prompts.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return Prompt::with(['category', 'tags', 'seller.profile'])
            ->approved()
            ->search($term)
            ->orderBy('purchases_count', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new prompt.
     */
    public function create(array $data): Prompt
    {
        return Prompt::create($data);
    }

    /**
     * Update an existing prompt.
     */
    public function update(Prompt $prompt, array $data): Prompt
    {
        $prompt->update($data);
        return $prompt->fresh();
    }

    /**
     * Delete a prompt.
     */
    public function delete(Prompt $prompt): bool
    {
        return $prompt->delete();
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount(Prompt $prompt): void
    {
        $prompt->incrementViewCount();
    }

    /**
     * Increment purchase count.
     */
    public function incrementPurchaseCount(Prompt $prompt): void
    {
        $prompt->incrementPurchaseCount();
    }

    /**
     * Update rating.
     */
    public function updateRating(Prompt $prompt, float $rating): void
    {
        $prompt->updateRating($rating);
    }

    /**
     * Sync tags for a prompt.
     */
    public function syncTags(Prompt $prompt, array $tagIds): void
    {
        $prompt->tags()->sync($tagIds);
    }
}
