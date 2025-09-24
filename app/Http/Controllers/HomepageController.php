<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomepageController extends Controller
{
    /**
     * Display the homepage with featured content and statistics.
     */
    public function index(): Response
    {
        // Get featured prompts
        $featuredPrompts = Prompt::with(['user', 'category', 'tags'])
            ->published()
            ->where('featured', true)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($prompt) {
                return [
                    'id' => $prompt->id,
                    'uuid' => $prompt->uuid,
                    'title' => $prompt->title,
                    'description' => $prompt->description,
                    'excerpt' => $prompt->excerpt,
                    'price' => $prompt->price,
                    'price_type' => $prompt->price_type,
                    'minimum_price' => $prompt->minimum_price,
                    'featured' => $prompt->featured,
                    'average_rating' => $prompt->getAverageRating(),
                    'purchase_count' => $prompt->getPurchaseCount(),
                    'user' => [
                        'id' => $prompt->user->id,
                        'name' => $prompt->user->name,
                    ],
                    'category' => $prompt->category ? [
                        'id' => $prompt->category->id,
                        'name' => $prompt->category->name,
                        'slug' => $prompt->category->slug,
                        'color' => $prompt->category->color,
                    ] : null,
                    'tags' => $prompt->tags->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'slug' => $tag->slug,
                        ];
                    }),
                ];
            });

        // Get popular prompts (most purchased)
        $popularPrompts = Prompt::with(['user', 'category', 'tags'])
            ->published()
            ->withCount('purchases')
            ->orderBy('purchases_count', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($prompt) {
                return [
                    'id' => $prompt->id,
                    'uuid' => $prompt->uuid,
                    'title' => $prompt->title,
                    'description' => $prompt->description,
                    'excerpt' => $prompt->excerpt,
                    'price' => $prompt->price ? (float) $prompt->price : 0.0,
                    'price_type' => $prompt->price_type,
                    'minimum_price' => $prompt->minimum_price ? (float) $prompt->minimum_price : null,
                    'featured' => $prompt->featured,
                    'average_rating' => (float) $prompt->getAverageRating(),
                    'purchase_count' => (int) $prompt->getPurchaseCount(),
                    'user' => [
                        'id' => $prompt->user->id,
                        'name' => $prompt->user->name,
                    ],
                    'category' => $prompt->category ? [
                        'id' => $prompt->category->id,
                        'name' => $prompt->category->name,
                        'slug' => $prompt->category->slug,
                        'color' => $prompt->category->color,
                    ] : null,
                    'tags' => $prompt->tags->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'slug' => $tag->slug,
                        ];
                    }),
                ];
            });

        // Get recent prompts
        $recentPrompts = Prompt::with(['user', 'category', 'tags'])
            ->published()
            ->orderBy('published_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($prompt) {
                return [
                    'id' => $prompt->id,
                    'uuid' => $prompt->uuid,
                    'title' => $prompt->title,
                    'description' => $prompt->description,
                    'excerpt' => $prompt->excerpt,
                    'price' => $prompt->price,
                    'price_type' => $prompt->price_type,
                    'minimum_price' => $prompt->minimum_price,
                    'featured' => $prompt->featured,
                    'average_rating' => $prompt->getAverageRating(),
                    'purchase_count' => $prompt->getPurchaseCount(),
                    'user' => [
                        'id' => $prompt->user->id,
                        'name' => $prompt->user->name,
                    ],
                    'category' => $prompt->category ? [
                        'id' => $prompt->category->id,
                        'name' => $prompt->category->name,
                        'slug' => $prompt->category->slug,
                        'color' => $prompt->category->color,
                    ] : null,
                    'tags' => $prompt->tags->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'slug' => $tag->slug,
                        ];
                    }),
                ];
            });

        // Get categories with prompt counts
        $categories = Category::with(['children'])
            ->where('is_active', true)
            ->whereNull('parent_id') // Only top-level categories
            ->withCount(['prompts' => function ($query) {
                $query->published();
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'uuid' => $category->uuid,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'icon' => $category->icon,
                    'color' => $category->color,
                    'prompts_count' => $category->prompts_count,
                ];
            });

        // Get platform statistics
        $stats = [
            'total_prompts' => Prompt::published()->count(),
            'total_users' => User::count(),
            'total_sales' => Purchase::count(),
        ];

        return Inertia::render('homepage', [
            'featuredPrompts' => $featuredPrompts,
            'popularPrompts' => $popularPrompts,
            'recentPrompts' => $recentPrompts,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }
}
