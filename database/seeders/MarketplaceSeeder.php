<?php

namespace Database\Seeders;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\Prompt\Models\Review;
use App\Modules\Prompt\Models\Tag;
use App\Modules\User\Models\User;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all sellers and buyers
        $sellers = User::where('role', 'seller')->get();
        $buyers = User::where('role', 'buyer')->get();
        $categories = Category::whereNotNull('parent_id')->get(); // Use subcategories
        $parentCategories = Category::whereNull('parent_id')->get();
        $tags = Tag::all();

        if ($sellers->isEmpty() || $categories->isEmpty()) {
            $this->command->error('Please run UsersTableSeeder and CategorySeeder first!');

            return;
        }

        // Create prompts
        $prompts = collect();
        $promptCount = 100;

        $this->command->info("Creating {$promptCount} prompts...");

        for ($i = 0; $i < $promptCount; $i++) {
            $seller = $sellers->random();
            $category = fake()->boolean(70) ? $categories->random() : $parentCategories->random();

            $prompt = Prompt::factory()->create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
            ]);

            // Attach random tags (2-5 tags per prompt)
            $promptTags = $tags->random(fake()->numberBetween(2, 5));
            $prompt->tags()->attach($promptTags->pluck('id'));

            $prompts->push($prompt);
        }

        $this->command->info("Created {$promptCount} prompts");

        // Create featured prompts
        $featuredCount = 10;
        $promptsToFeature = $prompts->random($featuredCount);

        foreach ($promptsToFeature as $prompt) {
            $prompt->update([
                'featured' => true,
                'rating' => fake()->randomFloat(2, 4.5, 5),
                'purchases_count' => fake()->numberBetween(50, 200),
            ]);
        }

        $this->command->info("Featured {$featuredCount} prompts");

        // Create purchases and reviews
        $purchaseCount = 0;
        $reviewCount = 0;

        foreach ($prompts as $prompt) {
            // Each prompt gets 0-10 purchases
            $numPurchases = fake()->numberBetween(0, 10);

            for ($j = 0; $j < $numPurchases; $j++) {
                $buyer = $buyers->random();

                // Check if buyer already purchased this prompt
                $existingPurchase = Purchase::where('buyer_id', $buyer->id)
                    ->where('prompt_id', $prompt->id)
                    ->first();

                if ($existingPurchase) {
                    continue;
                }

                $purchase = Purchase::factory()->create([
                    'buyer_id' => $buyer->id,
                    'prompt_id' => $prompt->id,
                    'price' => $prompt->price,
                    'platform_fee' => round($prompt->price * 0.15, 2),
                    'seller_earnings' => round($prompt->price * 0.85, 2),
                ]);

                $purchaseCount++;

                // 60% of purchases get a review
                if (fake()->boolean(60)) {
                    Review::factory()->create([
                        'prompt_id' => $prompt->id,
                        'user_id' => $buyer->id,
                        'purchase_id' => $purchase->id,
                    ]);

                    $reviewCount++;
                }
            }
        }

        $this->command->info("Created {$purchaseCount} purchases");
        $this->command->info("Created {$reviewCount} reviews");

        // Create some pending and draft prompts
        $pendingPrompts = 15;
        $draftPrompts = 10;

        for ($i = 0; $i < $pendingPrompts; $i++) {
            $seller = $sellers->random();
            $category = $categories->random();

            $prompt = Prompt::factory()->pending()->create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
            ]);

            $promptTags = $tags->random(fake()->numberBetween(2, 4));
            $prompt->tags()->attach($promptTags->pluck('id'));
        }

        for ($i = 0; $i < $draftPrompts; $i++) {
            $seller = $sellers->random();
            $category = $categories->random();

            $prompt = Prompt::factory()->draft()->create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
            ]);

            $promptTags = $tags->random(fake()->numberBetween(2, 4));
            $prompt->tags()->attach($promptTags->pluck('id'));
        }

        $this->command->info("Created {$pendingPrompts} pending prompts");
        $this->command->info("Created {$draftPrompts} draft prompts");

        // Create some rejected prompts
        $rejectedPrompts = 5;
        for ($i = 0; $i < $rejectedPrompts; $i++) {
            $seller = $sellers->random();
            $category = $categories->random();

            Prompt::factory()->rejected()->create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
            ]);
        }

        $this->command->info("Created {$rejectedPrompts} rejected prompts");

        // Create free prompts
        $freePrompts = 15;
        for ($i = 0; $i < $freePrompts; $i++) {
            $seller = $sellers->random();
            $category = $categories->random();

            $prompt = Prompt::factory()->free()->create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
            ]);

            $promptTags = $tags->random(fake()->numberBetween(2, 4));
            $prompt->tags()->attach($promptTags->pluck('id'));
        }

        $this->command->info("Created {$freePrompts} free prompts");

        // Update seller stats
        foreach ($sellers as $seller) {
            $sellerPrompts = Prompt::where('seller_id', $seller->id)->pluck('id');
            $sellerSales = Purchase::whereIn('prompt_id', $sellerPrompts)->count();
            $sellerEarnings = Purchase::whereIn('prompt_id', $sellerPrompts)
                ->where('status', 'completed')
                ->sum('seller_earnings');

            if ($seller->profile) {
                $seller->profile->update([
                    'total_sales' => $sellerSales,
                    'total_earnings' => $sellerEarnings,
                    'seller_verified' => $sellerSales >= 10, // Verify sellers with 10+ sales
                ]);
            }
        }

        $this->command->info('Updated seller statistics');
    }
}
