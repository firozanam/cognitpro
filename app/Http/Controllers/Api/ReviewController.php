<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prompt;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'prompt'])
            ->approved();

        // Filter by prompt
        if ($request->has('prompt_id')) {
            $query->where('prompt_id', $request->prompt_id);
        }

        // Filter by user's reviews
        if ($request->has('my_reviews')) {
            $query->where('user_id', Auth::id());
        }

        $reviews = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt_id' => 'required|exists:prompts,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'review_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $prompt = Prompt::findOrFail($data['prompt_id']);

        // Check if user has purchased this prompt
        $hasPurchased = $prompt->isPurchasedBy(Auth::user());
        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review prompts you have purchased',
            ], 422);
        }

        // Check if user already reviewed this prompt
        $existingReview = Review::where('user_id', Auth::id())
            ->where('prompt_id', $data['prompt_id'])
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this prompt',
            ], 422);
        }

        $data['user_id'] = Auth::id();
        $data['verified_purchase'] = true;

        $review = Review::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Review created successfully',
            'data' => $review->load(['user', 'prompt']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $review->load(['user', 'prompt']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        // Check if user owns this review
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'review_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $review->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review->load(['user', 'prompt']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review): JsonResponse
    {
        // Check if user owns this review
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Get current user's reviews.
     */
    public function myReviews(Request $request): JsonResponse
    {
        $query = Review::with(['prompt.user', 'prompt.category'])
            ->where('user_id', Auth::id());

        $reviews = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }
}
