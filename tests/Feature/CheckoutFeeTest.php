<?php

namespace Tests\Feature;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutFeeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function checkout_serves_the_platform_fee_rate(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::factory()->create();

        $prompt = Prompt::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'pricing_model' => 'fixed',
            'price' => 10,
        ]);

        $this->actingAs($buyer)->get("/checkout/{$prompt->id}")
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->component('purchases/checkout')
                    ->where('platform_fee_rate', 0.15);
            });
    }
}
