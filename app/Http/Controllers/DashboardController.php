<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\Review;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        if ($user->role === 'seller' || $user->role === 'admin') {
            return $this->sellerDashboard();
        } else {
            return $this->buyerDashboard();
        }
    }

    /**
     * Display the seller dashboard.
     */
    private function sellerDashboard(?AnalyticsService $analyticsService = null): Response
    {
        $user = Auth::user();
        $analyticsService = $analyticsService ?? app(AnalyticsService::class);

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

        // Calculate seller statistics using the service
        $stats = $analyticsService->getSellerStats($user);

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

    /**
     * Display seller analytics page.
     */
    public function analytics(AnalyticsService $analyticsService): Response
    {
        $user = Auth::user();

        try {
            // Get analytics data using the service with proper error handling
            $monthlyStats = $analyticsService->getMonthlyStats($user, 12);
            $topPrompts = $analyticsService->getTopPrompts($user, 10);
            $sellerStats = $analyticsService->getSellerStats($user);
            $revenueTrends = $analyticsService->getRevenueTrends($user, 30);

            // Ensure all data is properly formatted for frontend consumption
            return Inertia::render('dashboard/analytics', [
                'monthlyStats' => $monthlyStats->toArray(),
                'topPrompts' => $topPrompts->toArray(),
                'sellerStats' => $sellerStats,
                'revenueTrends' => $revenueTrends->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading analytics dashboard', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty data structures to prevent frontend errors
            return Inertia::render('dashboard/analytics', [
                'monthlyStats' => [],
                'topPrompts' => [],
                'sellerStats' => [
                    'total_prompts' => 0,
                    'published_prompts' => 0,
                    'draft_prompts' => 0,
                    'total_sales' => 0,
                    'total_revenue' => 0,
                    'average_rating' => 0,
                    'total_reviews' => 0,
                    'this_month_sales' => 0,
                    'this_month_revenue' => 0,
                ],
                'revenueTrends' => [],
            ]);
        }
    }

    /**
     * Display seller sales page.
     */
    public function sales(): Response
    {
        $user = Auth::user();

        $sales = Purchase::whereHas('prompt', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['prompt', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return Inertia::render('dashboard/sales', [
            'sales' => $sales,
        ]);
    }

    /**
     * Display seller payouts page.
     */
    public function payouts(): Response
    {
        $user = Auth::user();

        // This would integrate with actual payout system
        $payouts = collect(); // Placeholder for now

        $pendingEarnings = Purchase::whereHas('prompt', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->sum('price_paid') * 0.9; // Assuming 10% platform fee

        return Inertia::render('dashboard/payouts', [
            'payouts' => $payouts,
            'pendingEarnings' => $pendingEarnings,
        ]);
    }

    /**
     * Display seller reviews page.
     */
    public function sellerReviews(): Response
    {
        $user = Auth::user();

        $reviews = Review::whereHas('prompt', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['prompt', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return Inertia::render('dashboard/seller-reviews', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * Display buyer purchases page.
     */
    public function purchases(): Response
    {
        $user = Auth::user();

        $purchases = Purchase::where('user_id', $user->id)
            ->with(['prompt.user', 'prompt.category'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('dashboard/purchases', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Display buyer reviews page.
     */
    public function buyerReviews(): Response
    {
        $user = Auth::user();

        $reviews = Review::where('user_id', $user->id)
            ->with(['prompt.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('dashboard/buyer-reviews', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * Display buyer favorites page.
     */
    public function favorites(): Response
    {
        // This would require a favorites/wishlist system to be implemented
        $favorites = collect(); // Placeholder for now

        return Inertia::render('dashboard/favorites', [
            'favorites' => $favorites,
        ]);
    }
}
