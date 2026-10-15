<?php

namespace Tests\Feature;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Payment page authorization on the real Inertia surface. The
 * confirm-payment binding and signed-webhook flows are covered by
 * ConfirmPaymentBindingTest and StripeWebhookTest.
 */
class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;

    protected User $seller;

    protected Prompt $prompt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create(['role' => 'seller']);
        $this->buyer = User::factory()->create(['role' => 'buyer']);

        $category = Category::factory()->create();

        $this->prompt = Prompt::factory()->create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'status' => 'approved',
            'price' => 9.99,
        ]);
    }

    #[Test]
    public function guest_is_redirected_from_payment_page(): void
    {
        $this->get('/stripe/webhook')->assertStatus(405); // webhook is POST-only, proves no auth wall needed here

        $this->get('/purchases/1/stripe-payment')->assertRedirect('/login');
    }

    #[Test]
    public function buyer_cannot_open_another_users_payment_page(): void
    {
        $otherBuyer = User::factory()->create(['role' => 'buyer']);

        $purchase = Purchase::factory()->create([
            'buyer_id' => $otherBuyer->id,
            'prompt_id' => $this->prompt->id,
            'status' => 'pending',
            'price' => 9.99,
        ]);

        $this->actingAs($this->buyer)
            ->get("/purchases/{$purchase->id}/stripe-payment")
            ->assertForbidden();
    }

    #[Test]
    public function completed_purchase_redirects_away_from_payment_page(): void
    {
        $purchase = Purchase::factory()->create([
            'buyer_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
            'status' => 'completed',
            'price' => 9.99,
        ]);

        $this->actingAs($this->buyer)
            ->get("/purchases/{$purchase->id}/stripe-payment")
            ->assertRedirect(route('purchases.show', $purchase->id, absolute: false));
    }

    #[Test]
    public function unconfigured_payment_redirects_back_with_error(): void
    {
        config(['services.stripe.secret' => null, 'services.stripe.key' => null]);

        $purchase = Purchase::factory()->create([
            'buyer_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
            'status' => 'pending',
            'price' => 9.99,
        ]);

        $this->actingAs($this->buyer)
            ->from("/purchases/{$purchase->id}/stripe-payment")
            ->get("/purchases/{$purchase->id}/stripe-payment")
            ->assertRedirect("/purchases/{$purchase->id}/stripe-payment")
            ->assertSessionHas('error', 'Payment processing is not configured.');
    }

    #[Test]
    public function confirm_payment_requires_an_intent_id(): void
    {
        $purchase = Purchase::factory()->create([
            'buyer_id' => $this->buyer->id,
            'prompt_id' => $this->prompt->id,
            'status' => 'pending',
            'price' => 9.99,
        ]);

        $this->actingAs($this->buyer)
            ->postJson("/purchases/{$purchase->id}/confirm-payment", [])
            ->assertInvalid('payment_intent_id');
    }
}
