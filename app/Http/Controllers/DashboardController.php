<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index(): Response
    {
        $user = Auth::user();

        if ($user->isSeller() || $user->isAdmin()) {
            return $this->sellerDashboard();
        } else {
            return $this->buyerDashboard();
        }
    }

    /**
     * Display the seller dashboard.
     */
    private function sellerDashboard(): Response
    {
        $user = Auth::user();

        // Get seller's prompts
        $prompts = Prompt::with(['category', 'tags'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($prompt) {
                return [
                    'id' => $prompt->id,
                    'uuid' => $prompt->uuid,
                    'title' => $prompt->title,
                    'status' => $prompt->status,
                    'price' => $prompt->price ? (float) $prompt->price : 0.0,
                    'price_type' => $prompt->price_type,
                    'featured' => $prompt->featured,
                    'created_at' => $prompt->created_at->format('Y-m-d H:i:s'),
                    'published_at' => $prompt->published_at?->format('Y-m-d H:i:s'),
                    'purchase_count' => (int) $prompt->getPurchaseCount(),
                    'average_rating' => (float) $prompt->getAverageRating(),
                    'category' => $prompt->category ? [
                        'name' => $prompt->category->name,
                        'color' => $prompt->category->color,
                    ] : null,
                    'tags' => $prompt->tags->map(function ($tag) {
                        return [
                            'name' => $tag->name,
                        ];
                    }),
                ];
            });

        // Get recent purchases of seller's prompts
        $recentSales = Purchase::with(['prompt', 'user'])
            ->whereHas('prompt', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('purchased_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'price_paid' => $purchase->price_paid,
                    'purchased_at' => $purchase->purchased_at->format('Y-m-d H:i:s'),
                    'prompt' => [
                        'id' => $purchase->prompt->id,
                        'title' => $purchase->prompt->title,
                    ],
                    'buyer' => [
                        'name' => $purchase->user->name,
                    ],
                ];
            });

        // Get recent reviews on seller's prompts
        $recentReviews = Review::with(['prompt', 'user'])
            ->whereHas('prompt', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->approved()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'review_text' => $review->review_text,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                    'prompt' => [
                        'id' => $review->prompt->id,
                        'title' => $review->prompt->title,
                    ],
                    'reviewer' => [
                        'name' => $review->user->name,
                    ],
                ];
            });

        // Calculate seller statistics
        $stats = [
            'total_prompts' => Prompt::where('user_id', $user->id)->count(),
            'published_prompts' => Prompt::where('user_id', $user->id)->published()->count(),
            'draft_prompts' => Prompt::where('user_id', $user->id)->where('status', 'draft')->count(),
            'total_sales' => Purchase::whereHas('prompt', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
            'total_revenue' => Purchase::whereHas('prompt', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->sum('price_paid'),
            'average_rating' => Review::whereHas('prompt', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->approved()->avg('rating'),
            'total_reviews' => Review::whereHas('prompt', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->approved()->count(),
        ];

        return Inertia::render('dashboard/seller', [
            'prompts' => $prompts,
            'recentSales' => $recentSales,
            'recentReviews' => $recentReviews,
            'stats' => $stats,
        ]);
    }

    /**
     * Display the buyer dashboard.
     */
    private function buyerDashboard(): Response
    {
        $user = Auth::user();

        // Get user's purchases
        $purchases = Purchase::with(['prompt.user', 'prompt.category'])
            ->where('user_id', $user->id)
            ->orderBy('purchased_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'price_paid' => $purchase->price_paid,
                    'purchased_at' => $purchase->purchased_at->format('Y-m-d H:i:s'),
                    'prompt' => [
                        'id' => $purchase->prompt->id,
                        'uuid' => $purchase->prompt->uuid,
                        'title' => $purchase->prompt->title,
                        'excerpt' => $purchase->prompt->excerpt,
                        'user' => [
                            'name' => $purchase->prompt->user->name,
                        ],
                        'category' => $purchase->prompt->category ? [
                            'name' => $purchase->prompt->category->name,
                            'color' => $purchase->prompt->category->color,
                        ] : null,
                    ],
                ];
            });

        // Get user's reviews
        $reviews = Review::with(['prompt'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'review_text' => $review->review_text,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                    'prompt' => [
                        'id' => $review->prompt->id,
                        'title' => $review->prompt->title,
                    ],
                ];
            });

        // Calculate buyer statistics
        $stats = [
            'total_purchases' => Purchase::where('user_id', $user->id)->count(),
            'total_spent' => Purchase::where('user_id', $user->id)->sum('price_paid'),
            'total_reviews' => Review::where('user_id', $user->id)->count(),
            'favorite_category' => Purchase::with(['prompt.category'])
                ->where('user_id', $user->id)
                ->get()
                ->groupBy('prompt.category.name')
                ->sortByDesc(function ($purchases) {
                    return $purchases->count();
                })
                ->keys()
                ->first(),
        ];

        return Inertia::render('dashboard/buyer', [
            'purchases' => $purchases,
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }
}
