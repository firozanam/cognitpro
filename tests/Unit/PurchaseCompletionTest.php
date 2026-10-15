<?php

namespace Tests\Unit;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Services\PurchaseService;
use App\Modules\User\Models\User;
use App\Notifications\PurchaseConfirmationNotification;
use App\Notifications\SellerSaleNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PurchaseCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function newPurchase(): \App\Modules\Prompt\Models\Purchase
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
            'purchases_count' => 0,
        ]);

        return $this->app->make(PurchaseService::class)->initiatePurchase($buyer, $prompt);
    }

    public function test_complete_purchase_is_idempotent(): void
    {
        Notification::fake();

        $purchase = $this->newPurchase();
        $service = $this->app->make(PurchaseService::class);

        $service->completePurchase($purchase, 'stripe', 'pi_once');
        $service->completePurchase($purchase->fresh(), 'stripe', 'pi_once');

        $this->assertSame(1, (int) $purchase->prompt->fresh()->purchases_count);
        $this->assertDatabaseHas('purchases', ['id' => $purchase->id, 'payment_id' => 'pi_once']);

        Notification::assertSentToTimes($purchase->buyer, PurchaseConfirmationNotification::class, 1);
        Notification::assertSentToTimes($purchase->prompt->seller, SellerSaleNotification::class, 1);
    }
}
