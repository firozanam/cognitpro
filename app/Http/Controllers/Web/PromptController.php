<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Prompt;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PromptController extends Controller
{
    /**
     * Display a listing of prompts with filters.
     */
    public function index(Request $request): Response
    {
        $query = Prompt::with(['user', 'category', 'tags'])
            ->published();

        // Apply filters
        $filters = $request->only([
            'search', 'category_id', 'tags', 'price_type', 
            'min_price', 'max_price', 'sort_by', 'sort_order', 'featured'
        ]);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

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

        // Price range filters
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Featured filter
        if (isset($filters['featured']) && $filters['featured']) {
            $query->where('featured', true);
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

        // Paginate results
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
                'is_purchased' => Auth::check() ? $prompt->isPurchasedBy(Auth::user()) : false,
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

        // Get categories for filters
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Get tags for filters
        $tags = Tag::withCount('prompts')
            ->having('prompts_count', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('prompts/index', [
            'prompts' => $prompts,
            'categories' => $categories,
            'tags' => $tags,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified prompt.
     */
    public function show(Prompt $prompt): Response
    {
        // Check if prompt is published or user owns it
        if (!$prompt->isPublished() && (!Auth::check() || $prompt->user_id !== Auth::id())) {
            abort(404);
        }

        // Load relationships
        $prompt->load([
            'user.profile',
            'category',
            'tags',
            'approvedReviews.user',
            'purchases'
        ]);

        // Transform prompt data
        $promptData = [
            'id' => $prompt->id,
            'uuid' => $prompt->uuid,
            'title' => $prompt->title,
            'description' => $prompt->description,
            'content' => $prompt->content,
            'excerpt' => $prompt->excerpt,
            'price' => $prompt->price ? (float) $prompt->price : 0.0,
            'price_type' => $prompt->price_type,
            'minimum_price' => $prompt->minimum_price ? (float) $prompt->minimum_price : null,
            'featured' => $prompt->featured,
            'version' => $prompt->version,
            'published_at' => $prompt->published_at?->format('Y-m-d H:i:s'),
            'average_rating' => (float) $prompt->getAverageRating(),
            'purchase_count' => (int) $prompt->getPurchaseCount(),
            'is_purchased' => Auth::check() ? $prompt->isPurchasedBy(Auth::user()) : false,
            'user' => [
                'id' => $prompt->user->id,
                'name' => $prompt->user->name,
                'email' => $prompt->user->email,
                'profile' => $prompt->user->profile ? [
                    'bio' => $prompt->user->profile->bio,
                    'website' => $prompt->user->profile->website,
                    'location' => $prompt->user->profile->location,
                ] : null,
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
            'reviews' => $prompt->approvedReviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'review_text' => $review->review_text,
                    'verified_purchase' => $review->verified_purchase,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                    'user' => [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                    ],
                ];
            }),
        ];

        // Get related prompts from same category
        $relatedPrompts = Prompt::with(['user', 'category', 'tags'])
            ->published()
            ->where('category_id', $prompt->category_id)
            ->where('id', '!=', $prompt->id)
            ->limit(4)
            ->get()
            ->map(function ($relatedPrompt) {
                return [
                    'id' => $relatedPrompt->id,
                    'uuid' => $relatedPrompt->uuid,
                    'title' => $relatedPrompt->title,
                    'excerpt' => $relatedPrompt->excerpt,
                    'price' => $relatedPrompt->price,
                    'price_type' => $relatedPrompt->price_type,
                    'average_rating' => $relatedPrompt->getAverageRating(),
                    'purchase_count' => $relatedPrompt->getPurchaseCount(),
                    'user' => [
                        'name' => $relatedPrompt->user->name,
                    ],
                ];
            });

        return Inertia::render('prompts/show', [
            'prompt' => $promptData,
            'relatedPrompts' => $relatedPrompts,
        ]);
    }

    /**
     * Show the form for creating a new prompt.
     */
    public function create(): Response
    {
        // Get categories for the form
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'color']);

        // Get tags for the form
        $tags = Tag::orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('prompts/create', [
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Store a newly created prompt in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price_type' => 'required|in:fixed,pay_what_you_want,free',
            'price' => 'required_if:price_type,fixed|nullable|numeric|min:0',
            'minimum_price' => 'required_if:price_type,pay_what_you_want|nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'nullable|in:draft,published',
        ]);

        $validatedData['user_id'] = Auth::id();
        $validatedData['status'] = $validatedData['status'] ?? 'draft';

        $prompt = Prompt::create($validatedData);

        // Attach tags if provided
        if (isset($validatedData['tags'])) {
            $prompt->tags()->attach($validatedData['tags']);
        }

        return redirect()->route('prompts.manage')->with('success', 'Prompt created successfully!');
    }

    /**
     * Show the form for editing the specified prompt.
     */
    public function edit(Prompt $prompt): Response
    {
        // Check if user owns the prompt
        if ($prompt->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own prompts.');
        }

        // Load relationships
        $prompt->load(['category', 'tags']);

        // Get categories for the form
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'color']);

        // Get tags for the form
        $tags = Tag::orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Transform prompt data
        $promptData = [
            'id' => $prompt->id,
            'uuid' => $prompt->uuid,
            'title' => $prompt->title,
            'description' => $prompt->description,
            'content' => $prompt->content,
            'category_id' => $prompt->category_id,
            'price_type' => $prompt->price_type,
            'price' => $prompt->price,
            'minimum_price' => $prompt->minimum_price,
            'status' => $prompt->status,
            'tags' => $prompt->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ];
            }),
        ];

        return Inertia::render('prompts/edit', [
            'prompt' => $promptData,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Display seller's prompt management page.
     */
    public function manage(Request $request): Response
    {
        $query = Prompt::with(['category', 'tags'])
            ->where('user_id', Auth::id());

        // Apply filters
        $filters = $request->only(['status', 'search', 'sort_by', 'sort_order']);

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
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

        // Paginate results
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
                'status' => $prompt->status,
                'featured' => $prompt->featured,
                'created_at' => $prompt->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $prompt->updated_at->format('Y-m-d H:i:s'),
                'published_at' => $prompt->published_at?->format('Y-m-d H:i:s'),
                'average_rating' => (float) $prompt->getAverageRating(),
                'purchase_count' => (int) $prompt->getPurchaseCount(),
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

        return Inertia::render('dashboard/prompts', [
            'prompts' => $prompts,
            'filters' => $filters,
        ]);
    }
}
