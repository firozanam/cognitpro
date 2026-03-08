<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Initialize stats
        $stats = [
            'total_purchases' => 0,
            'total_spent' => 0,
            'total_sales' => 0,
            'total_earnings' => 0,
            'total_prompts' => 0,
            'total_views' => 0,
            'cart_items' => 0,
        ];

        $recentPurchases = [];
        $recentSales = [];
        $prompts = [];

        // Get buyer stats (for buyers, sellers, and admins)
        if (in_array($user->role, ['buyer', 'seller', 'admin'])) {
            $purchaseQuery = Purchase::where('buyer_id', $user->id)
                ->where('status', 'completed');

            $stats['total_purchases'] = $purchaseQuery->count();
            $stats['total_spent'] = $purchaseQuery->sum('price');

            $recentPurchases = Purchase::where('buyer_id', $user->id)
                ->with(['prompt.seller', 'prompt.category'])
                ->orderBy('purchased_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($purchase) {
                    return [
                        'id' => $purchase->id,
                        'order_number' => $purchase->order_number,
                        'price' => (float) $purchase->price,
                        'status' => $purchase->status,
                        'purchased_at' => $purchase->purchased_at?->toISOString(),
                        'prompt' => [
                            'id' => $purchase->prompt->id,
                            'title' => $purchase->prompt->title,
                            'slug' => $purchase->prompt->slug,
                            'ai_model' => $purchase->prompt->ai_model,
                            'seller' => [
                                'name' => $purchase->prompt->seller->name,
                            ],
                        ],
                    ];
                });

            // Cart count
            $stats['cart_items'] = $user->cart()->count();
        }

        // Get seller stats (for sellers and admins)
        if (in_array($user->role, ['seller', 'admin'])) {
            // Prompt stats
            $promptQuery = Prompt::where('seller_id', $user->id);
            $stats['total_prompts'] = $promptQuery->count();
            $stats['total_views'] = $promptQuery->sum('views_count');

            // Sales stats
            $saleQuery = Purchase::whereHas('prompt', function ($query) use ($user) {
                $query->where('seller_id', $user->id);
            })->where('status', 'completed');

            $stats['total_sales'] = $saleQuery->count();
            $stats['total_earnings'] = $saleQuery->sum('seller_earnings');

            // Recent sales
            $recentSales = Purchase::whereHas('prompt', function ($query) use ($user) {
                $query->where('seller_id', $user->id);
            })
                ->with(['prompt', 'buyer'])
                ->orderBy('purchased_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'order_number' => $sale->order_number,
                        'price' => (float) $sale->price,
                        'seller_earnings' => (float) $sale->seller_earnings,
                        'status' => $sale->status,
                        'purchased_at' => $sale->purchased_at?->toISOString(),
                        'prompt' => [
                            'id' => $sale->prompt->id,
                            'title' => $sale->prompt->title,
                            'slug' => $sale->prompt->slug,
                        ],
                        'buyer' => [
                            'id' => $sale->buyer->id,
                            'name' => $sale->buyer->name,
                        ],
                    ];
                });

            // Recent prompts
            $prompts = Prompt::where('seller_id', $user->id)
                ->with('category')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($prompt) {
                    return [
                        'id' => $prompt->id,
                        'title' => $prompt->title,
                        'slug' => $prompt->slug,
                        'status' => $prompt->status,
                        'price' => (float) $prompt->price,
                        'views_count' => $prompt->views_count,
                        'purchases_count' => $prompt->purchases_count,
                        'rating' => (float) $prompt->rating,
                        'rating_count' => $prompt->rating_count,
                    ];
                });
        }

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'recentPurchases' => $recentPurchases,
            'recentSales' => $recentSales,
            'prompts' => $prompts,
        ]);
    }
}
