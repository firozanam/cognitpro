<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnalyticsService $analyticsService;
    private User $seller;
    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->analyticsService = new AnalyticsService();
        
        // Create test users
        $this->seller = User::factory()->create(['role' => 'seller']);
        $this->buyer = User::factory()->create(['role' => 'buyer']);
    }

    public function test_get_monthly_stats_with_no_purchases()
    {
        $stats = $this->analyticsService->getMonthlyStats($this->seller);
        
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $stats);
        $this->assertTrue($stats->isEmpty());
    }

    public function test_get_monthly_stats_with_purchases()
    {
        // Create a category and prompts
        $category = Category::factory()->create();
        $prompt1 = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $prompt2 = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Create purchases in different months
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // Create another buyer for the second purchase to avoid unique constraint
        $buyer2 = User::factory()->create(['role' => 'buyer']);

        Purchase::factory()->create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt1->id,
            'price_paid' => 10.00,
            'created_at' => $currentMonth,
        ]);

        Purchase::factory()->create([
            'user_id' => $buyer2->id,
            'prompt_id' => $prompt2->id,
            'price_paid' => 15.00,
            'created_at' => $lastMonth,
        ]);

        $stats = $this->analyticsService->getMonthlyStats($this->seller, 12);

        $this->assertCount(2, $stats);

        // Check current month stats
        $currentMonthStats = $stats->first();
        $this->assertEquals($currentMonth->month, $currentMonthStats['month']);
        $this->assertEquals($currentMonth->year, $currentMonthStats['year']);
        $this->assertEquals(1, $currentMonthStats['sales']);
        $this->assertEquals(10.00, $currentMonthStats['revenue']);

        // Check last month stats
        $lastMonthStats = $stats->last();
        $this->assertEquals($lastMonth->month, $lastMonthStats['month']);
        $this->assertEquals($lastMonth->year, $lastMonthStats['year']);
        $this->assertEquals(1, $lastMonthStats['sales']);
        $this->assertEquals(15.00, $lastMonthStats['revenue']);
    }

    public function test_get_top_prompts_with_no_prompts()
    {
        $topPrompts = $this->analyticsService->getTopPrompts($this->seller);
        
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $topPrompts);
        $this->assertTrue($topPrompts->isEmpty());
    }

    public function test_get_top_prompts_with_purchases()
    {
        $category = Category::factory()->create();

        // Create prompts with different purchase counts
        $prompt1 = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'title' => 'Popular Prompt',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $prompt2 = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'title' => 'Less Popular Prompt',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Create multiple buyers to avoid unique constraint
        $buyers = User::factory()->count(4)->create(['role' => 'buyer']);

        // Create more purchases for prompt1 (3 purchases)
        Purchase::factory()->create([
            'user_id' => $buyers[0]->id,
            'prompt_id' => $prompt1->id,
            'price_paid' => 10.00,
        ]);

        Purchase::factory()->create([
            'user_id' => $buyers[1]->id,
            'prompt_id' => $prompt1->id,
            'price_paid' => 10.00,
        ]);

        Purchase::factory()->create([
            'user_id' => $buyers[2]->id,
            'prompt_id' => $prompt1->id,
            'price_paid' => 10.00,
        ]);

        // Create one purchase for prompt2
        Purchase::factory()->create([
            'user_id' => $buyers[3]->id,
            'prompt_id' => $prompt2->id,
            'price_paid' => 15.00,
        ]);

        $topPrompts = $this->analyticsService->getTopPrompts($this->seller, 10);

        $this->assertCount(2, $topPrompts);

        // Check that prompt1 is first (more purchases)
        $firstPrompt = $topPrompts->first();
        $this->assertEquals($prompt1->id, $firstPrompt['id']);
        $this->assertEquals('Popular Prompt', $firstPrompt['title']);
        $this->assertEquals(3, $firstPrompt['purchases_count']);

        // Check that prompt2 is second
        $secondPrompt = $topPrompts->last();
        $this->assertEquals($prompt2->id, $secondPrompt['id']);
        $this->assertEquals('Less Popular Prompt', $secondPrompt['title']);
        $this->assertEquals(1, $secondPrompt['purchases_count']);
    }

    public function test_get_seller_stats()
    {
        $category = Category::factory()->create();

        // Create prompts with different statuses
        $publishedPrompt = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'draft',
        ]);

        // Create multiple buyers for purchases
        $buyers = User::factory()->count(2)->create(['role' => 'buyer']);

        // Create purchases
        Purchase::factory()->create([
            'user_id' => $buyers[0]->id,
            'prompt_id' => $publishedPrompt->id,
            'price_paid' => 20.00,
        ]);

        Purchase::factory()->create([
            'user_id' => $buyers[1]->id,
            'prompt_id' => $publishedPrompt->id,
            'price_paid' => 20.00,
        ]);

        // Create reviews
        Review::factory()->create([
            'user_id' => $buyers[0]->id,
            'prompt_id' => $publishedPrompt->id,
            'rating' => 5,
            'is_approved' => true,
        ]);

        $stats = $this->analyticsService->getSellerStats($this->seller);

        $this->assertEquals(2, $stats['total_prompts']);
        $this->assertEquals(1, $stats['published_prompts']);
        $this->assertEquals(1, $stats['draft_prompts']);
        $this->assertEquals(2, $stats['total_sales']);
        $this->assertEquals(40.00, $stats['total_revenue']);
        $this->assertEquals(5.0, $stats['average_rating']);
        $this->assertEquals(1, $stats['total_reviews']);
    }

    public function test_get_revenue_trends()
    {
        $category = Category::factory()->create();
        $prompt1 = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $prompt2 = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Create purchases on different days
        $today = Carbon::now();
        $yesterday = Carbon::now()->subDay();

        // Create multiple buyers
        $buyers = User::factory()->count(3)->create(['role' => 'buyer']);

        Purchase::factory()->create([
            'user_id' => $buyers[0]->id,
            'prompt_id' => $prompt1->id,
            'price_paid' => 10.00,
            'created_at' => $today,
        ]);

        Purchase::factory()->create([
            'user_id' => $buyers[1]->id,
            'prompt_id' => $prompt2->id,
            'price_paid' => 15.00,
            'created_at' => $yesterday,
        ]);

        Purchase::factory()->create([
            'user_id' => $buyers[2]->id,
            'prompt_id' => $prompt1->id,
            'price_paid' => 15.00,
            'created_at' => $yesterday,
        ]);

        $trends = $this->analyticsService->getRevenueTrends($this->seller, 7);

        $this->assertCount(2, $trends);

        // Check yesterday's data (should be first due to ascending order)
        $yesterdayData = $trends->first();
        $this->assertEquals($yesterday->format('Y-m-d'), $yesterdayData['date']);
        $this->assertEquals(2, $yesterdayData['sales']);
        $this->assertEquals(30.00, $yesterdayData['revenue']);

        // Check today's data
        $todayData = $trends->last();
        $this->assertEquals($today->format('Y-m-d'), $todayData['date']);
        $this->assertEquals(1, $todayData['sales']);
        $this->assertEquals(10.00, $todayData['revenue']);
    }

    public function test_caching_works()
    {
        Cache::flush();
        
        $category = Category::factory()->create();
        $prompt = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // First call should cache the result
        $stats1 = $this->analyticsService->getSellerStats($this->seller);
        
        // Create new data that shouldn't appear in cached result
        Purchase::factory()->create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 10.00,
        ]);
        
        // Second call should return cached result (no new purchase)
        $stats2 = $this->analyticsService->getSellerStats($this->seller);
        
        $this->assertEquals($stats1['total_sales'], $stats2['total_sales']);
        $this->assertEquals(0, $stats2['total_sales']); // Should still be 0 due to cache
        
        // Clear cache and call again
        $this->analyticsService->clearCache($this->seller);
        $stats3 = $this->analyticsService->getSellerStats($this->seller);
        
        $this->assertEquals(1, $stats3['total_sales']); // Should now show the new purchase
    }

    public function test_get_platform_stats()
    {
        // Create test data
        $category = Category::factory()->create();
        $seller2 = User::factory()->create(['role' => 'seller']);
        $buyer2 = User::factory()->create(['role' => 'buyer']);

        $prompt = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        Purchase::factory()->create([
            'user_id' => $buyer2->id, // Use the created buyer2 to avoid unique constraint
            'prompt_id' => $prompt->id,
            'price_paid' => 25.00,
        ]);

        Review::factory()->create([
            'user_id' => $buyer2->id, // Use the created buyer2
            'prompt_id' => $prompt->id,
            'rating' => 4,
            'is_approved' => true,
        ]);

        $platformStats = $this->analyticsService->getPlatformStats();

        $this->assertEquals(4, $platformStats['total_users']); // 2 sellers + 2 buyers
        $this->assertEquals(2, $platformStats['total_sellers']);
        $this->assertEquals(2, $platformStats['total_buyers']);
        $this->assertEquals(1, $platformStats['total_prompts']);
        $this->assertEquals(1, $platformStats['published_prompts']);
        $this->assertEquals(1, $platformStats['total_sales']);
        $this->assertEquals(25.00, $platformStats['total_revenue']);
        $this->assertEquals(1, $platformStats['total_reviews']);
        $this->assertEquals(4.0, $platformStats['average_rating']);
    }
}
