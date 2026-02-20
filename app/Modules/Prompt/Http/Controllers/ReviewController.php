<?php

namespace App\Modules\Prompt\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\Prompt\Models\Review;
use App\Modules\Prompt\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Display reviews for a prompt.
     */
    public function index(Request $request, Prompt $prompt): Response
    {
        $reviews = $this->reviewService->getPromptReviews(
            $prompt->id,
            $request->input('per_page', 10)
        );

        $stats = $this->reviewService->getPromptRatingStats($prompt->id);

        return Inertia::render('prompts/reviews', [
            'prompt' => $prompt,
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new review.
     */
    public function create(Request $request, Purchase $purchase): Response|RedirectResponse
    {
        // Ensure user owns this purchase
        if ($purchase->buyer_id !== $request->user()->id) {
            abort(403);
        }

        // Check if already reviewed
        if ($purchase->hasReview()) {
            return redirect()->route('purchases.show', $purchase->id)
                ->with('info', 'You have already reviewed this purchase.');
        }

        $purchase->load(['prompt', 'prompt.seller']);

        return Inertia::render('reviews/create', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Store a newly created review.
     */
    public function store(Request $request, Purchase $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $review = $this->reviewService->createReview(
                $request->user(),
                $purchase,
                $validated
            );

            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', 'Review submitted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review): Response
    {
        $review->load(['user', 'prompt', 'prompt.seller', 'purchase']);

        return Inertia::render('reviews/show', [
            'review' => $review,
        ]);
    }

    /**
     * Show the form for editing a review.
     */
    public function edit(Request $request, Review $review): Response|RedirectResponse
    {
        // Ensure user owns this review
        if ($review->user_id !== $request->user()->id) {
            abort(403);
        }

        $review->load(['prompt', 'purchase']);

        return Inertia::render('reviews/edit', [
            'review' => $review,
        ]);
    }

    /**
     * Update the specified review.
     */
    public function update(Request $request, Review $review): RedirectResponse
    {
        // Ensure user owns this review
        if ($review->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->reviewService->updateReview($review, $request->user(), $validated);

            return redirect()->route('reviews.show', $review->id)
                ->with('success', 'Review updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified review.
     */
    public function destroy(Request $request, Review $review): RedirectResponse
    {
        try {
            $this->reviewService->deleteReview($review, $request->user());

            return redirect()->route('purchases.index')
                ->with('success', 'Review deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add seller response to a review.
     */
    public function respond(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'response' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->reviewService->addSellerResponse(
                $review,
                $request->user(),
                $validated['response']
            );

            return back()->with('success', 'Response added successfully!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark a review as helpful.
     */
    public function helpful(Review $review): RedirectResponse
    {
        $this->reviewService->markAsHelpful($review);

        return back()->with('success', 'Thank you for your feedback!');
    }

    /**
     * Display reviews by the authenticated user.
     */
    public function myReviews(Request $request): Response
    {
        $reviews = $this->reviewService->getUserReviews(
            $request->user()->id,
            $request->input('per_page', 10)
        );

        return Inertia::render('reviews/my-reviews', [
            'reviews' => $reviews,
        ]);
    }
}
