<?php

namespace App\Modules\Prompt\Services;

use App\Modules\Prompt\Contracts\PromptRepositoryInterface;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromptService
{
    public function __construct(
        protected PromptRepositoryInterface $promptRepository
    ) {}

    /**
     * Get all prompts with pagination and filters.
     */
    public function getPrompts(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->promptRepository->getAllPaginated($perPage, $filters);
    }

    /**
     * Get a prompt by ID.
     */
    public function getPromptById(int $id): ?Prompt
    {
        return $this->promptRepository->findById($id);
    }

    /**
     * Get a prompt by slug.
     */
    public function getPromptBySlug(string $slug): ?Prompt
    {
        return $this->promptRepository->findBySlug($slug);
    }

    /**
     * Get prompts by seller.
     */
    public function getPromptsBySeller(int $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->promptRepository->getBySellerId($sellerId, $perPage);
    }

    /**
     * Get prompts by category.
     */
    public function getPromptsByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->promptRepository->getByCategoryId($categoryId, $perPage);
    }

    /**
     * Get featured prompts.
     */
    public function getFeaturedPrompts(int $limit = 10): Collection
    {
        return $this->promptRepository->getFeatured($limit);
    }

    /**
     * Search prompts.
     */
    public function searchPrompts(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->promptRepository->search($term, $perPage);
    }

    /**
     * Create a new prompt.
     */
    public function createPrompt(array $data): Prompt
    {
        return DB::transaction(function () use ($data) {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title']);
            }

            // Handle pricing model
            if ($data['pricing_model'] === 'free') {
                $data['price'] = 0;
                $data['min_price'] = null;
            }

            // Extract tags from data
            $tags = $data['tags'] ?? [];
            unset($data['tags']);

            // Create the prompt
            $prompt = $this->promptRepository->create($data);

            // Sync tags
            if (!empty($tags)) {
                $tagIds = $this->getOrCreateTagIds($tags);
                $this->promptRepository->syncTags($prompt, $tagIds);
            }

            return $prompt->load(['category', 'tags', 'seller.profile']);
        });
    }

    /**
     * Update an existing prompt.
     */
    public function updatePrompt(Prompt $prompt, array $data): Prompt
    {
        return DB::transaction(function () use ($prompt, $data) {
            // Generate new slug if title changed
            if (isset($data['title']) && $data['title'] !== $prompt->title && empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title'], $prompt->id);
            }

            // Handle pricing model
            if (isset($data['pricing_model'])) {
                if ($data['pricing_model'] === 'free') {
                    $data['price'] = 0;
                    $data['min_price'] = null;
                }
            }

            // Extract tags from data
            $tags = $data['tags'] ?? null;
            unset($data['tags']);

            // Update the prompt
            $prompt = $this->promptRepository->update($prompt, $data);

            // Sync tags if provided
            if ($tags !== null) {
                $tagIds = $this->getOrCreateTagIds($tags);
                $this->promptRepository->syncTags($prompt, $tagIds);
            }

            return $prompt->load(['category', 'tags', 'seller.profile']);
        });
    }

    /**
     * Delete a prompt.
     */
    public function deletePrompt(Prompt $prompt): bool
    {
        return $this->promptRepository->delete($prompt);
    }

    /**
     * Submit prompt for approval.
     */
    public function submitForApproval(Prompt $prompt): Prompt
    {
        if (!$prompt->isDraft()) {
            throw new \Exception('Only draft prompts can be submitted for approval.');
        }

        return $this->promptRepository->update($prompt, [
            'status' => 'pending',
        ]);
    }

    /**
     * Approve a prompt.
     */
    public function approvePrompt(Prompt $prompt): Prompt
    {
        return $this->promptRepository->update($prompt, [
            'status' => 'approved',
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject a prompt.
     */
    public function rejectPrompt(Prompt $prompt, string $reason): Prompt
    {
        return $this->promptRepository->update($prompt, [
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Feature/unfeature a prompt.
     */
    public function toggleFeatured(Prompt $prompt): Prompt
    {
        return $this->promptRepository->update($prompt, [
            'featured' => !$prompt->featured,
        ]);
    }

    /**
     * Record a view on a prompt.
     */
    public function recordView(Prompt $prompt): void
    {
        $this->promptRepository->incrementViewCount($prompt);
    }

    /**
     * Record a purchase on a prompt.
     */
    public function recordPurchase(Prompt $prompt): void
    {
        $this->promptRepository->incrementPurchaseCount($prompt);
    }

    /**
     * Update prompt rating.
     */
    public function updateRating(Prompt $prompt, float $newRating): void
    {
        $this->promptRepository->updateRating($prompt, $newRating);
    }

    /**
     * Generate a unique slug for the prompt.
     */
    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        $query = Prompt::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $count++;
            $query = Prompt::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Get or create tag IDs from tag names.
     */
    protected function getOrCreateTagIds(array $tags): array
    {
        $tagIds = [];

        foreach ($tags as $tag) {
            if (is_numeric($tag)) {
                $tagIds[] = (int) $tag;
            } else {
                $tagModel = Tag::firstOrCreate(
                    ['slug' => Str::slug($tag)],
                    ['name' => $tag]
                );
                $tagIds[] = $tagModel->id;
            }
        }

        return array_unique($tagIds);
    }
}
