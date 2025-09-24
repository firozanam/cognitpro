<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): Response
    {
        $categories = Category::with(['children'])
            ->where('is_active', true)
            ->whereNull('parent_id') // Only top-level categories
            ->withCount(['prompts' => function ($query) {
                $query->published();
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
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
                    'children' => $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'prompts_count' => $child->prompts()->published()->count(),
                        ];
                    }),
                ];
            });

        return Inertia::render('categories/index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Display the specified category with its prompts.
     */
    public function show(Category $category, Request $request): Response
    {
        if (!$category->is_active) {
            abort(404);
        }

        // Get prompts in this category
        $query = $category->prompts()
            ->with(['user', 'category', 'tags'])
            ->published();

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'popularity') {
            $query->withCount('purchases')->orderBy('purchases_count', $sortOrder);
        } elseif ($sortBy === 'rating') {
            $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $prompts = $query->paginate($request->get('per_page', 15));

        // Transform prompt data
        $prompts->getCollection()->transform(function ($prompt) {
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
                'published_at' => $prompt->published_at?->format('Y-m-d H:i:s'),
                'average_rating' => (float) $prompt->getAverageRating(),
                'purchase_count' => (int) $prompt->getPurchaseCount(),
                'user' => [
                    'id' => $prompt->user->id,
                    'name' => $prompt->user->name,
                ],
                'category' => [
                    'id' => $prompt->category->id,
                    'name' => $prompt->category->name,
                    'slug' => $prompt->category->slug,
                    'color' => $prompt->category->color,
                ],
                'tags' => $prompt->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    ];
                }),
            ];
        });

        // Get category data
        $categoryData = [
            'id' => $category->id,
            'uuid' => $category->uuid,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'icon' => $category->icon,
            'color' => $category->color,
            'prompts_count' => $category->prompts()->published()->count(),
        ];

        // Get subcategories if any
        $subcategories = $category->children()
            ->where('is_active', true)
            ->withCount(['prompts' => function ($query) {
                $query->published();
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($subcategory) {
                return [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                    'prompts_count' => $subcategory->prompts_count,
                ];
            });

        return Inertia::render('categories/show', [
            'category' => $categoryData,
            'subcategories' => $subcategories,
            'prompts' => $prompts,
            'filters' => [
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }
}
