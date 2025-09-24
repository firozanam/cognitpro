<?php

namespace App\Services;

use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    /**
     * Cache duration for analytics data (in minutes)
     */
    private const CACHE_DURATION = 30;

    /**
     * Get monthly sales statistics for a seller
     *
     * @param User $user
     * @param int $months Number of months to retrieve (default: 12)
     * @return Collection
     */
    public function getMonthlyStats(User $user, int $months = 12): Collection
    {
        $cacheKey = "analytics.monthly_stats.{$user->id}.{$months}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($user, $months) {
            try {
                // Get all purchases for the user's prompts
                $purchases = Purchase::whereHas('prompt', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->select('created_at', 'price_paid')
                ->where('created_at', '>=', Carbon::now()->subMonths($months))
                ->orderBy('created_at', 'desc')
                ->get();

                // Group purchases by month/year using Carbon
                $monthlyStats = $purchases->groupBy(function ($purchase) {
                    return $purchase->created_at->format('Y-m');
                })->map(function ($monthPurchases, $yearMonth) {
                    [$year, $month] = explode('-', $yearMonth);
                    return [
                        'month' => (int) $month,
                        'year' => (int) $year,
                        'sales' => $monthPurchases->count(),
                        'revenue' => $monthPurchases->sum('price_paid')
                    ];
                })->sortByDesc(function ($stats) {
                    // Sort by year and month descending
                    return $stats['year'] * 100 + $stats['month'];
                })->take($months)->values();

                return $monthlyStats;

            } catch (\Exception $e) {
                Log::error('Error generating monthly stats', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                return collect();
            }
        });
    }

    /**
     * Get top performing prompts for a seller
     *
     * @param User $user
     * @param int $limit Number of prompts to retrieve (default: 10)
     * @return Collection
     */
    public function getTopPrompts(User $user, int $limit = 10): Collection
    {
        $cacheKey = "analytics.top_prompts.{$user->id}.{$limit}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($user, $limit) {
            try {
                return Prompt::where('user_id', $user->id)
                    ->withCount('purchases')
                    ->with(['purchases' => function ($query) {
                        $query->select('prompt_id', DB::raw('SUM(price_paid) as total_revenue'))
                            ->groupBy('prompt_id');
                    }])
                    ->orderBy('purchases_count', 'desc')
                    ->limit($limit)
                    ->get()
                    ->map(function ($prompt) {
                        return [
                            'id' => $prompt->id,
                            'uuid' => $prompt->uuid,
                            'title' => $prompt->title,
                            'purchases_count' => $prompt->purchases_count,
                            'total_revenue' => $prompt->purchases->first()?->total_revenue ?? 0,
                            'average_rating' => $prompt->getAverageRating(),
                            'status' => $prompt->status,
                            'created_at' => $prompt->created_at->format('Y-m-d H:i:s'),
                        ];
                    });

            } catch (\Exception $e) {
                Log::error('Error generating top prompts', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                return collect();
            }
        });
    }

    /**
     * Get comprehensive seller statistics
     *
     * @param User $user
     * @return array
     */
    public function getSellerStats(User $user): array
    {
        $cacheKey = "analytics.seller_stats.{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($user) {
            try {
                return [
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
                    })->approved()->avg('rating') ?? 0,
                    'total_reviews' => Review::whereHas('prompt', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })->approved()->count(),
                    'this_month_sales' => Purchase::whereHas('prompt', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
                    'this_month_revenue' => Purchase::whereHas('prompt', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })->where('created_at', '>=', Carbon::now()->startOfMonth())->sum('price_paid'),
                ];

            } catch (\Exception $e) {
                Log::error('Error generating seller stats', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                return [
                    'total_prompts' => 0,
                    'published_prompts' => 0,
                    'draft_prompts' => 0,
                    'total_sales' => 0,
                    'total_revenue' => 0,
                    'average_rating' => 0,
                    'total_reviews' => 0,
                    'this_month_sales' => 0,
                    'this_month_revenue' => 0,
                ];
            }
        });
    }

    /**
     * Get revenue trends for a seller
     *
     * @param User $user
     * @param int $days Number of days to analyze (default: 30)
     * @return Collection
     */
    public function getRevenueTrends(User $user, int $days = 30): Collection
    {
        $cacheKey = "analytics.revenue_trends.{$user->id}.{$days}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($user, $days) {
            try {
                $purchases = Purchase::whereHas('prompt', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->select('created_at', 'price_paid')
                ->where('created_at', '>=', Carbon::now()->subDays($days))
                ->orderBy('created_at', 'asc')
                ->get();

                // Group by day
                return $purchases->groupBy(function ($purchase) {
                    return $purchase->created_at->format('Y-m-d');
                })->map(function ($dayPurchases, $date) {
                    return [
                        'date' => $date,
                        'sales' => $dayPurchases->count(),
                        'revenue' => $dayPurchases->sum('price_paid'),
                    ];
                })->values();

            } catch (\Exception $e) {
                Log::error('Error generating revenue trends', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                return collect();
            }
        });
    }

    /**
     * Clear analytics cache for a user
     *
     * @param User $user
     * @return void
     */
    public function clearCache(User $user): void
    {
        $patterns = [
            "analytics.monthly_stats.{$user->id}.*",
            "analytics.top_prompts.{$user->id}.*",
            "analytics.seller_stats.{$user->id}",
            "analytics.revenue_trends.{$user->id}.*",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Get platform-wide analytics (admin only)
     *
     * @return array
     */
    public function getPlatformStats(): array
    {
        $cacheKey = "analytics.platform_stats";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () {
            try {
                return [
                    'total_users' => User::count(),
                    'total_sellers' => User::where('role', 'seller')->count(),
                    'total_buyers' => User::where('role', 'buyer')->count(),
                    'total_prompts' => Prompt::count(),
                    'published_prompts' => Prompt::published()->count(),
                    'total_sales' => Purchase::count(),
                    'total_revenue' => Purchase::sum('price_paid'),
                    'total_reviews' => Review::approved()->count(),
                    'average_rating' => Review::approved()->avg('rating') ?? 0,
                    'this_month_sales' => Purchase::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
                    'this_month_revenue' => Purchase::where('created_at', '>=', Carbon::now()->startOfMonth())->sum('price_paid'),
                ];

            } catch (\Exception $e) {
                Log::error('Error generating platform stats', [
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }
}
