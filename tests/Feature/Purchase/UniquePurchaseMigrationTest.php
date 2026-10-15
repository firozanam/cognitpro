<?php

namespace Tests\Feature\Purchase;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UniquePurchaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The unique (buyer_id, prompt_id) index must be addable over dirty
     * production data: duplicate rows are deduplicated (completed row kept,
     * otherwise the newest) instead of aborting the deploy (DB-02 / I-3).
     */
    public function test_migration_deduplicates_existing_rows_before_unique_index(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'fixed',
            'price' => 10.00,
            'status' => 'approved',
        ]);

        // Roll back everything after the purchases table exists pre-unique...
        Artisan::call('migrate:rollback', ['--step' => 4]);

        // ...stack duplicates exactly like the old check-then-create race did.
        $insert = fn (string $status, int $order) => \DB::table('purchases')->insert([
            'buyer_id' => $buyer->id,
            'prompt_id' => $prompt->id,
            'order_number' => "ORD-DUP-{$order}",
            'price' => 10.00,
            'platform_fee' => 1.50,
            'seller_earnings' => 8.50,
            'payment_method' => 'stripe',
            'payment_id' => "pi_{$order}",
            'status' => $status,
            'created_at' => now()->addMinutes($order),
            'updated_at' => now()->addMinutes($order),
        ]);
        $insert('pending', 1);
        $insert('completed', 2);
        $insert('pending', 3);

        Artisan::call('migrate');

        // Exactly one row survives, and it is the completed one.
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseHas('purchases', [
            'buyer_id' => $buyer->id,
            'prompt_id' => $prompt->id,
            'status' => 'completed',
            'order_number' => 'ORD-DUP-2',
        ]);
    }
}
