<?php

namespace Tests\Feature\Seller;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SellerSalesAvatarTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function seller_sales_include_the_buyers_avatar_url(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $buyer->profile()->create(['avatar' => 'avatars/buyer.png']);
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'pricing_model' => 'free',
            'price' => 0,
        ]);

        Purchase::factory()->create([
            'buyer_id' => $buyer->id,
            'prompt_id' => $prompt->id,
            'status' => 'completed',
        ]);

        $this->actingAs($seller)->get('/seller/sales')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->component('seller/sales')
                    ->where('sales.data.0.buyer.avatar', asset('storage/avatars/buyer.png'));
            });
    }

    #[Test]
    public function user_without_avatar_serializes_null_avatar(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->avatar);
    }
}
