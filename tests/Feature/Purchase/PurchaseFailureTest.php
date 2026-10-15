<?php

namespace Tests\Feature\Purchase;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Services\PurchaseService;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseFailureTest extends TestCase
{
    use RefreshDatabase;

    public function test_fail_purchase_persists_failed_status(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'pricing_model' => 'fixed',
            'price' => 10,
            'status' => 'approved',
        ]);

        $purchase = $this->app->make(PurchaseService::class)
            ->initiatePurchase($buyer, $prompt);

        $this->app->make(PurchaseService::class)
            ->failPurchase($purchase, 'card_declined');

        $this->assertTrue($purchase->fresh()->isFailed());
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'failed',
        ]);
    }
}
