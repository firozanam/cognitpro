<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromptService
{
    /**
     * Create a new prompt with tags.
     */
    public function createPrompt(User $user, array $data, array $tagIds = []): Prompt
    {
        return DB::transaction(function () use ($user, $data, $tagIds) {
            $data['user_id'] = $user->id;
            
            $prompt = Prompt::create($data);

            if (!empty($tagIds)) {
                $prompt->tags()->attach($tagIds);
            }

            Log::info('Prompt created', [
                'prompt_id' => $prompt->id,
                'user_id' => $user->id,
                'title' => $prompt->title,
            ]);

            return $prompt;
        });
    }

    /**
     * Update a prompt with tags.
     */
    public function updatePrompt(Prompt $prompt, array $data, array $tagIds = null): Prompt
    {
        return DB::transaction(function () use ($prompt, $data, $tagIds) {
            // Set published_at when publishing for the first time
            if (isset($data['status']) && $data['status'] === 'published' && !$prompt->published_at) {
                $data['published_at'] = now();
            }

            $prompt->update($data);

            if ($tagIds !== null) {
                $prompt->tags()->sync($tagIds);
            }

            Log::info('Prompt updated', [
                'prompt_id' => $prompt->id,
                'user_id' => $prompt->user_id,
                'changes' => array_keys($data),
            ]);

            return $prompt;
        });
    }

    /**
     * Get featured prompts.
     */
    public function getFeaturedPrompts(int $limit = 10): Collection
    {
        return Prompt::with(['user', 'category', 'tags'])
            ->published()
            ->featured()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get popular prompts based on purchase count.
     */
    public function getPopularPrompts(int $limit = 10): Collection
    {
        return Prompt::with(['user', 'category', 'tags'])
            ->published()
            ->withCount('purchases')
            ->orderBy('purchases_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recently published prompts.
     */
    public function getRecentPrompts(int $limit = 10): Collection
    {
        return Prompt::with(['user', 'category', 'tags'])
            ->published()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search prompts with filters.
     */
    public function searchPrompts(array $filters = [], int $perPage = 15)
    {
        $query = Prompt::with(['user', 'category', 'tags', 'reviews'])
            ->published();

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Tags filter
        if (!empty($filters['tags'])) {
            $tags = is_array($filters['tags']) ? $filters['tags'] : explode(',', $filters['tags']);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('slug', $tags);
            });
        }

        // Price type filter
        if (!empty($filters['price_type'])) {
            $query->where('price_type', $filters['price_type']);
        }

        // Price range filter
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Search text
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        if ($sortBy === 'popularity') {
            $query->withCount('purchases')->orderBy('purchases_count', $sortOrder);
        } elseif ($sortBy === 'rating') {
            $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get prompts by category.
     */
    public function getPromptsByCategory(Category $category, int $perPage = 15)
    {
        return Prompt::with(['user', 'category', 'tags'])
            ->published()
            ->where('category_id', $category->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get prompts by tag.
     */
    public function getPromptsByTag(Tag $tag, int $perPage = 15)
    {
        return $tag->prompts()
            ->with(['user', 'category', 'tags'])
            ->published()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get seller's prompts.
     */
    public function getSellerPrompts(User $seller, string $status = null, int $perPage = 15)
    {
        $query = Prompt::with(['category', 'tags'])
            ->where('user_id', $seller->id);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get prompt statistics for a seller.
     */
    public function getSellerStats(User $seller): array
    {
        $prompts = Prompt::where('user_id', $seller->id)->get();

        return [
            'total_prompts' => $prompts->count(),
            'published_prompts' => $prompts->where('status', 'published')->count(),
            'draft_prompts' => $prompts->where('status', 'draft')->count(),
            'total_sales' => $prompts->sum(function ($prompt) {
                return $prompt->purchases()->count();
            }),
            'total_revenue' => $prompts->sum(function ($prompt) {
                return $prompt->purchases()->sum('price_paid');
            }),
        ];
    }

    /**
     * Duplicate a prompt for versioning.
     */
    public function duplicatePrompt(Prompt $originalPrompt, array $changes = []): Prompt
    {
        return DB::transaction(function () use ($originalPrompt, $changes) {
            $data = $originalPrompt->toArray();
            
            // Remove fields that shouldn't be duplicated
            unset($data['id'], $data['uuid'], $data['created_at'], $data['updated_at']);
            
            // Apply changes
            $data = array_merge($data, $changes);
            
            // Increment version
            $data['version'] = $originalPrompt->version + 1;
            $data['status'] = 'draft';
            $data['published_at'] = null;

            $newPrompt = Prompt::create($data);

            // Copy tags
            $tagIds = $originalPrompt->tags()->pluck('tags.id')->toArray();
            if (!empty($tagIds)) {
                $newPrompt->tags()->attach($tagIds);
            }

            Log::info('Prompt duplicated', [
                'original_prompt_id' => $originalPrompt->id,
                'new_prompt_id' => $newPrompt->id,
                'version' => $newPrompt->version,
            ]);

            return $newPrompt;
        });
    }
}
