<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;
    private User $buyer;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seller = User::factory()->create(['role' => 'seller']);
        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_seller_can_access_analytics_page()
    {
        $response = $this->actingAs($this->seller)
            ->get(route('dashboard.analytics'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('dashboard/analytics')
                ->has('monthlyStats')
                ->has('topPrompts')
                ->has('sellerStats')
                ->has('revenueTrends')
        );
    }

    public function test_buyer_cannot_access_analytics_page()
    {
        $response = $this->actingAs($this->buyer)
            ->get(route('dashboard.analytics'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_analytics_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard.analytics'));

        $response->assertStatus(200);
    }

    public function test_analytics_page_with_real_data()
    {
        // Create test data
        $category = Category::factory()->create();

        $prompt1 = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'title' => 'Test Prompt 1',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $prompt2 = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'title' => 'Test Prompt 2',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Create multiple buyers to avoid unique constraint
        $buyers = User::factory()->count(6)->create(['role' => 'buyer']);

        // Create purchases in current month
        Purchase::factory()->create([
            'user_id' => $buyers[0]->id,
            'prompt_id' => $prompt1->id,
            'price_paid' => 10.00,
            'created_at' => Carbon::now(),
        ]);

        Purchase::factory()->create([
            'user_id' => $buyers[1]->id,
            'prompt_id' => $prompt1->id,
            'price_paid' => 10.00,
            'created_at' => Carbon::now(),
        ]);

        Purchase::factory()->create([
            'user_id' => $buyers[2]->id,
            'prompt_id' => $prompt1->id,
            'price_paid' => 10.00,
            'created_at' => Carbon::now(),
        ]);

        Purchase::factory()->create([
            'user_id' => $buyers[3]->id,
            'prompt_id' => $prompt2->id,
            'price_paid' => 15.00,
            'created_at' => Carbon::now(),
        ]);

        // Create purchases in last month
        Purchase::factory()->create([
            'user_id' => $buyers[4]->id,
            'prompt_id' => $prompt1->id,
            'price_paid' => 12.00,
            'created_at' => Carbon::now()->subMonth(),
        ]);

        Purchase::factory()->create([
            'user_id' => $buyers[5]->id,
            'prompt_id' => $prompt2->id,
            'price_paid' => 12.00,
            'created_at' => Carbon::now()->subMonth(),
        ]);

        // Create reviews
        Review::factory()->create([
            'user_id' => $buyers[0]->id,
            'prompt_id' => $prompt1->id,
            'rating' => 5,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($this->seller)
            ->get(route('dashboard.analytics'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/analytics')
                ->has('monthlyStats', 2) // Should have 2 months of data
                ->has('topPrompts', 2) // Should have 2 prompts
                ->has('sellerStats')
                ->where('sellerStats.total_prompts', 2)
                ->where('sellerStats.published_prompts', 2)
                ->where('sellerStats.total_sales', 6)
                ->where('sellerStats.total_revenue', 69) // 3*10 + 1*15 + 2*12
                ->where('sellerStats.total_reviews', 1)
                ->has('revenueTrends')
        );
    }

    public function test_analytics_monthly_stats_structure()
    {
        $category = Category::factory()->create();
        $prompt = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        Purchase::factory()->create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 20.00,
            'created_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->seller)
            ->get(route('dashboard.analytics'));

        $response->assertStatus(200);
        
        $response->assertInertia(fn ($page) => 
            $page->component('dashboard/analytics')
                ->has('monthlyStats.0', fn ($monthStats) =>
                    $monthStats->has('month')
                        ->has('year')
                        ->has('sales')
                        ->has('revenue')
                        ->where('month', Carbon::now()->month)
                        ->where('year', Carbon::now()->year)
                        ->where('sales', 1)
                        ->where('revenue', 20)
                )
        );
    }

    public function test_analytics_top_prompts_structure()
    {
        $category = Category::factory()->create();
        $prompt = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'title' => 'Popular Prompt',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Create multiple buyers to avoid unique constraint
        $buyers = User::factory()->count(2)->create(['role' => 'buyer']);

        Purchase::factory()->create([
            'user_id' => $buyers[0]->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 25.00,
        ]);

        Purchase::factory()->create([
            'user_id' => $buyers[1]->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 25.00,
        ]);

        Review::factory()->create([
            'user_id' => $buyers[0]->id,
            'prompt_id' => $prompt->id,
            'rating' => 4,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($this->seller)
            ->get(route('dashboard.analytics'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/analytics')
                ->has('topPrompts.0', fn ($topPrompt) =>
                    $topPrompt->has('id')
                        ->has('uuid')
                        ->has('title')
                        ->has('purchases_count')
                        ->has('total_revenue')
                        ->has('average_rating')
                        ->has('status')
                        ->has('created_at')
                        ->where('title', 'Popular Prompt')
                        ->where('purchases_count', 2)
                        ->where('total_revenue', 50)
                        ->where('average_rating', 4)
                        ->where('status', 'published')
                )
        );
    }

    public function test_analytics_seller_stats_structure()
    {
        $category = Category::factory()->create();
        
        // Create published prompt
        $publishedPrompt = Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
        
        // Create draft prompt
        Prompt::factory()->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'draft',
        ]);

        // Create multiple buyers to avoid unique constraint
        $buyers = User::factory()->count(2)->create(['role' => 'buyer']);

        // Create purchase this month
        Purchase::factory()->create([
            'user_id' => $buyers[0]->id,
            'prompt_id' => $publishedPrompt->id,
            'price_paid' => 30.00,
            'created_at' => Carbon::now(),
        ]);

        // Create purchase last month
        Purchase::factory()->create([
            'user_id' => $buyers[1]->id,
            'prompt_id' => $publishedPrompt->id,
            'price_paid' => 20.00,
            'created_at' => Carbon::now()->subMonth(),
        ]);

        $response = $this->actingAs($this->seller)
            ->get(route('dashboard.analytics'));

        $response->assertStatus(200);
        
        $response->assertInertia(fn ($page) => 
            $page->component('dashboard/analytics')
                ->has('sellerStats', fn ($stats) =>
                    $stats->has('total_prompts')
                        ->has('published_prompts')
                        ->has('draft_prompts')
                        ->has('total_sales')
                        ->has('total_revenue')
                        ->has('average_rating')
                        ->has('total_reviews')
                        ->has('this_month_sales')
                        ->has('this_month_revenue')
                        ->where('total_prompts', 2)
                        ->where('published_prompts', 1)
                        ->where('draft_prompts', 1)
                        ->where('total_sales', 2)
                        ->where('total_revenue', 50)
                        ->where('this_month_sales', 1)
                        ->where('this_month_revenue', 30)
                )
        );
    }

    public function test_analytics_with_no_data()
    {
        $response = $this->actingAs($this->seller)
            ->get(route('dashboard.analytics'));

        $response->assertStatus(200);
        
        $response->assertInertia(fn ($page) => 
            $page->component('dashboard/analytics')
                ->has('monthlyStats', 0)
                ->has('topPrompts', 0)
                ->has('sellerStats', fn ($stats) =>
                    $stats->where('total_prompts', 0)
                        ->where('published_prompts', 0)
                        ->where('draft_prompts', 0)
                        ->where('total_sales', 0)
                        ->where('total_revenue', 0)
                        ->where('average_rating', 0)
                        ->where('total_reviews', 0)
                        ->where('this_month_sales', 0)
                        ->where('this_month_revenue', 0)
                )
                ->has('revenueTrends', 0)
        );
    }

    public function test_analytics_performance_with_large_dataset()
    {
        $category = Category::factory()->create();
        
        // Create multiple prompts
        $prompts = Prompt::factory()->count(5)->create([
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Create many buyers for purchases
        $buyers = User::factory()->count(100)->create(['role' => 'buyer']);
        $buyerIndex = 0;

        // Create many purchases across different months
        foreach ($prompts as $prompt) {
            for ($i = 0; $i < 12; $i++) {
                $purchaseCount = rand(1, 5); // Reduced to avoid too many buyers
                for ($j = 0; $j < $purchaseCount; $j++) {
                    if ($buyerIndex < count($buyers)) {
                        Purchase::factory()->create([
                            'user_id' => $buyers[$buyerIndex]->id,
                            'prompt_id' => $prompt->id,
                            'price_paid' => rand(5, 50),
                            'created_at' => Carbon::now()->subMonths($i),
                        ]);
                        $buyerIndex++;
                    }
                }
            }
        }

        $startTime = microtime(true);
        
        $response = $this->actingAs($this->seller)
            ->get(route('dashboard.analytics'));

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        
        // Assert that the response time is reasonable (less than 2 seconds)
        $this->assertLessThan(2.0, $executionTime, 'Analytics page should load within 2 seconds');
        
        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/analytics')
                ->has('monthlyStats')
                ->has('topPrompts')
                ->has('sellerStats')
                ->has('revenueTrends')
        );
    }

    public function test_analytics_handles_null_undefined_data_gracefully()
    {
        // Mock the AnalyticsService to return null/empty data to test error handling
        $this->mock(AnalyticsService::class, function ($mock) {
            $mock->shouldReceive('getMonthlyStats')->andReturn(collect());
            $mock->shouldReceive('getTopPrompts')->andReturn(collect());
            $mock->shouldReceive('getSellerStats')->andReturn([
                'total_prompts' => 0,
                'published_prompts' => 0,
                'draft_prompts' => 0,
                'total_sales' => 0,
                'total_revenue' => 0,
                'average_rating' => 0,
                'total_reviews' => 0,
                'this_month_sales' => 0,
                'this_month_revenue' => 0,
            ]);
            $mock->shouldReceive('getRevenueTrends')->andReturn(collect());
        });

        $response = $this->actingAs($this->seller)
            ->get(route('dashboard.analytics'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/analytics')
                ->has('monthlyStats', 0)
                ->has('topPrompts', 0)
                ->has('sellerStats', fn ($stats) =>
                    $stats->where('total_prompts', 0)
                        ->where('published_prompts', 0)
                        ->where('draft_prompts', 0)
                        ->where('total_sales', 0)
                        ->where('total_revenue', 0)
                        ->where('average_rating', 0)
                        ->where('total_reviews', 0)
                        ->where('this_month_sales', 0)
                        ->where('this_month_revenue', 0)
                )
                ->has('revenueTrends', 0)
        );
    }

    public function test_analytics_handles_service_exceptions_gracefully()
    {
        // Mock the AnalyticsService to throw an exception
        $this->mock(AnalyticsService::class, function ($mock) {
            $mock->shouldReceive('getMonthlyStats')->andThrow(new \Exception('Database connection failed'));
            $mock->shouldReceive('getTopPrompts')->andThrow(new \Exception('Database connection failed'));
            $mock->shouldReceive('getSellerStats')->andThrow(new \Exception('Database connection failed'));
            $mock->shouldReceive('getRevenueTrends')->andThrow(new \Exception('Database connection failed'));
        });

        $response = $this->actingAs($this->seller)
            ->get(route('dashboard.analytics'));

        // Should still return 200 with empty data structures
        $response->assertStatus(200);

        $response->assertInertia(fn ($page) =>
            $page->component('dashboard/analytics')
                ->has('monthlyStats', 0)
                ->has('topPrompts', 0)
                ->has('sellerStats', fn ($stats) =>
                    $stats->where('total_prompts', 0)
                        ->where('published_prompts', 0)
                        ->where('draft_prompts', 0)
                        ->where('total_sales', 0)
                        ->where('total_revenue', 0)
                        ->where('average_rating', 0)
                        ->where('total_reviews', 0)
                        ->where('this_month_sales', 0)
                        ->where('this_month_revenue', 0)
                )
                ->has('revenueTrends', 0)
        );
    }
}
