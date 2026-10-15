<?php

namespace Tests\Feature;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpireStalePurchasesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function pending_purchases_older_than_a_day_expire(): void
    {
        [$stale, $fresh, $completed] = $this->purchases(withStale: true);

        $this->artisan('purchases:expire-stale')->expectsOutputToContain('1 stale purchase');

        $this->assertSame('failed', $stale->fresh()->status);
        $this->assertSame('pending', $fresh->fresh()->status);
        $this->assertSame('completed', $completed->fresh()->status);
    }

    #[Test]
    public function nothing_stale_reports_zero(): void
    {
        $this->purchases(withStale: false);

        $this->artisan('purchases:expire-stale')->expectsOutputToContain('0 stale purchases');
    }

    #[Test]
    public function the_task_is_on_the_schedule(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('purchases:expire-stale');
    }

    /**
     * @return array{0: Purchase, 1: Purchase, 2: Purchase}
     */
    private function purchases(bool $withStale): array
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $category = Category::factory()->create();

        $make = fn (string $status) => Purchase::factory()->create([
            'buyer_id' => $buyer->id,
            'prompt_id' => Prompt::factory()->create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'status' => 'approved',
            ])->id,
            'status' => $status,
        ]);

        $stale = $make('pending');
        $fresh = $make('pending');
        $completed = $make('completed');

        if ($withStale) {
            DB::table('purchases')->where('id', $stale->id)->update(['created_at' => now()->subDays(2)]);
        }

        return [$stale, $fresh, $completed];
    }
}
