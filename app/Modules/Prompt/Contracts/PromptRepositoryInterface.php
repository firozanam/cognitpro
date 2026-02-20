<?php

namespace App\Modules\Prompt\Contracts;

use App\Modules\Prompt\Models\Prompt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PromptRepositoryInterface
{
    /**
     * Get all prompts with pagination.
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get prompt by ID.
     */
    public function findById(int $id): ?Prompt;

    /**
     * Get prompt by slug.
     */
    public function findBySlug(string $slug): ?Prompt;

    /**
     * Get prompts by seller ID.
     */
    public function getBySellerId(int $sellerId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get prompts by category ID.
     */
    public function getByCategoryId(int $categoryId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get featured prompts.
     */
    public function getFeatured(int $limit = 10): Collection;

    /**
     * Get prompts by AI model.
     */
    public function getByAiModel(string $model, int $perPage = 15): LengthAwarePaginator;

    /**
     * Search prompts.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new prompt.
     */
    public function create(array $data): Prompt;

    /**
     * Update an existing prompt.
     */
    public function update(Prompt $prompt, array $data): Prompt;

    /**
     * Delete a prompt.
     */
    public function delete(Prompt $prompt): bool;

    /**
     * Increment view count.
     */
    public function incrementViewCount(Prompt $prompt): void;

    /**
     * Increment purchase count.
     */
    public function incrementPurchaseCount(Prompt $prompt): void;

    /**
     * Update rating.
     */
    public function updateRating(Prompt $prompt, float $rating): void;

    /**
     * Sync tags for a prompt.
     */
    public function syncTags(Prompt $prompt, array $tagIds): void;
}
