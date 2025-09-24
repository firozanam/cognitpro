<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PromptController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Prompt::with(['user', 'category', 'tags', 'reviews'])
            ->published();

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by tags
        if ($request->has('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('slug', $tags);
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Filter by price type
        if ($request->has('price_type')) {
            $query->where('price_type', $request->price_type);
        }

        // Filter by featured
        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        // Sort
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

        return response()->json([
            'success' => true,
            'data' => $prompts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price_type' => 'required|in:fixed,pay_what_you_want,free',
            'price' => 'required_if:price_type,fixed|nullable|numeric|min:0',
            'minimum_price' => 'required_if:price_type,pay_what_you_want|nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id();

        $prompt = Prompt::create($data);

        // Attach tags if provided
        if (isset($data['tags'])) {
            $prompt->tags()->attach($data['tags']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prompt created successfully',
            'data' => $prompt->load(['user', 'category', 'tags']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Prompt $prompt): JsonResponse
    {
        // Check if prompt is published or user owns it
        if (!$prompt->isPublished() && $prompt->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Prompt not found',
            ], 404);
        }

        $prompt->load([
            'user',
            'category',
            'tags',
            'approvedReviews.user',
            'purchases'
        ]);

        // Add computed attributes
        $prompt->average_rating = (float) $prompt->getAverageRating();
        $prompt->purchase_count = (int) $prompt->getPurchaseCount();
        $prompt->is_purchased = Auth::check() ? $prompt->isPurchasedBy(Auth::user()) : false;

        return response()->json([
            'success' => true,
            'data' => $prompt,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prompt $prompt): JsonResponse
    {
        // Check if user owns the prompt
        if ($prompt->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'price_type' => 'sometimes|required|in:fixed,pay_what_you_want,free',
            'price' => 'required_if:price_type,fixed|nullable|numeric|min:0',
            'minimum_price' => 'required_if:price_type,pay_what_you_want|nullable|numeric|min:0',
            'status' => 'sometimes|in:draft,published,archived',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Set published_at when publishing
        if (isset($data['status']) && $data['status'] === 'published' && !$prompt->published_at) {
            $data['published_at'] = now();
        }

        $prompt->update($data);

        // Update tags if provided
        if (isset($data['tags'])) {
            $prompt->tags()->sync($data['tags']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prompt updated successfully',
            'data' => $prompt->load(['user', 'category', 'tags']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prompt $prompt): JsonResponse
    {
        // Check if user owns the prompt
        if ($prompt->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if prompt has purchases
        if ($prompt->purchases()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete prompt with existing purchases',
            ], 422);
        }

        $prompt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prompt deleted successfully',
        ]);
    }

    /**
     * Get seller's prompts (including drafts).
     */
    public function sellerPrompts(Request $request): JsonResponse
    {
        $query = Prompt::with(['category', 'tags'])
            ->where('user_id', Auth::id());

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $prompts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $prompts,
        ]);
    }

    /**
     * Get user's own prompts (for sellers who are also buyers).
     */
    public function myPrompts(Request $request): JsonResponse
    {
        $query = Prompt::with(['category', 'tags'])
            ->where('user_id', Auth::id());

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $prompts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $prompts,
        ]);
    }

    /**
     * Get prompt content (only for purchasers or owners).
     */
    public function getContent(Prompt $prompt): JsonResponse
    {
        // Check if user owns the prompt or has purchased it
        if ($prompt->user_id !== Auth::id() && !$prompt->isPurchasedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Purchase required to view content.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'content' => $prompt->content,
                'prompt_id' => $prompt->id,
                'title' => $prompt->title,
            ],
        ]);
    }

    /**
     * Advanced search for prompts.
     */
    public function search(Request $request): JsonResponse
    {
        $query = Prompt::with(['user', 'category', 'tags', 'reviews'])
            ->published();

        // Search term
        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Tags filter
        if ($request->has('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('slug', $tags);
            });
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Price type filter
        if ($request->has('price_type')) {
            $query->where('price_type', $request->price_type);
        }

        // Sort
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

        return response()->json([
            'success' => true,
            'data' => $prompts,
        ]);
    }
}
