<?php

namespace Tests\Unit;

use App\Notifications\PurchaseConfirmationNotification;
use App\Notifications\PromptApprovedNotification;
use App\Notifications\PromptRejectedNotification;
use App\Notifications\SellerSaleNotification;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function purchase_confirmation_notification_has_correct_subject(): void
    {
        $user = User::factory()->create();
        
        $notification = new PurchaseConfirmationNotification([
            'purchase_id' => 1,
            'order_number' => 'ORD-123',
            'prompt_title' => 'Test Prompt',
            'price' => 9.99,
        ]);

        $mail = $notification->toMail($user);

        $this->assertEquals('Your Purchase Confirmation', $mail->subject);
    }

    /** @test */
    public function purchase_confirmation_notification_contains_order_details(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        
        $notification = new PurchaseConfirmationNotification([
            'purchase_id' => 1,
            'order_number' => 'ORD-123',
            'prompt_title' => 'Test Prompt',
            'price' => 9.99,
        ]);

        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        $this->assertStringContainsString('ORD-123', $rendered);
        $this->assertStringContainsString('Test Prompt', $rendered);
        $this->assertStringContainsString('9.99', $rendered);
    }

    /** @test */
    public function prompt_approved_notification_has_correct_subject(): void
    {
        $user = User::factory()->create();
        
        $notification = new PromptApprovedNotification([
            'id' => 1,
            'title' => 'Test Prompt',
            'slug' => 'test-prompt',
            'price' => 9.99,
        ]);

        $mail = $notification->toMail($user);

        $this->assertEquals('Your Prompt Has Been Approved!', $mail->subject);
    }

    /** @test */
    public function prompt_approved_notification_contains_prompt_details(): void
    {
        $user = User::factory()->create(['name' => 'Jane Doe']);
        
        $notification = new PromptApprovedNotification([
            'id' => 1,
            'title' => 'My Awesome Prompt',
            'slug' => 'my-awesome-prompt',
            'price' => 19.99,
        ]);

        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        $this->assertStringContainsString('My Awesome Prompt', $rendered);
        $this->assertStringContainsString('19.99', $rendered);
    }

    /** @test */
    public function prompt_rejected_notification_has_correct_subject(): void
    {
        $user = User::factory()->create();
        
        $notification = new PromptRejectedNotification([
            'id' => 1,
            'title' => 'Test Prompt',
            'rejection_reason' => 'Does not meet quality standards',
        ]);

        $mail = $notification->toMail($user);

        $this->assertEquals('Your Prompt Needs Revision', $mail->subject);
    }

    /** @test */
    public function prompt_rejected_notification_contains_reason(): void
    {
        $user = User::factory()->create(['name' => 'Seller']);
        
        $notification = new PromptRejectedNotification([
            'id' => 1,
            'title' => 'Test Prompt',
            'rejection_reason' => 'Please improve the description',
        ]);

        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        $this->assertStringContainsString('Test Prompt', $rendered);
        $this->assertStringContainsString('Please improve the description', $rendered);
    }

    /** @test */
    public function seller_sale_notification_has_correct_subject(): void
    {
        $user = User::factory()->create();
        
        $notification = new SellerSaleNotification([
            'purchase_id' => 1,
            'prompt_title' => 'Test Prompt',
            'price' => 9.99,
            'seller_earnings' => 8.49,
            'order_number' => 'ORD-123',
        ]);

        $mail = $notification->toMail($user);

        $this->assertEquals('You Made a Sale! 🎉', $mail->subject);
    }

    /** @test */
    public function seller_sale_notification_contains_earnings(): void
    {
        $user = User::factory()->create(['name' => 'Seller']);
        
        $notification = new SellerSaleNotification([
            'purchase_id' => 1,
            'prompt_title' => 'Premium Prompt',
            'price' => 29.99,
            'seller_earnings' => 25.49,
            'order_number' => 'ORD-456',
        ]);

        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        $this->assertStringContainsString('Premium Prompt', $rendered);
        $this->assertStringContainsString('29.99', $rendered);
        $this->assertStringContainsString('25.49', $rendered);
        $this->assertStringContainsString('ORD-456', $rendered);
    }

    /** @test */
    public function notifications_can_be_serialized_to_array(): void
    {
        $user = User::factory()->create();
        
        $purchaseNotification = new PurchaseConfirmationNotification([
            'purchase_id' => 1,
            'order_number' => 'ORD-123',
            'prompt_title' => 'Test Prompt',
            'price' => 9.99,
        ]);

        $array = $purchaseNotification->toArray($user);

        $this->assertArrayHasKey('purchase_id', $array);
        $this->assertArrayHasKey('order_number', $array);
        $this->assertArrayHasKey('prompt_title', $array);
        $this->assertArrayHasKey('price', $array);
    }

    /** @test */
    public function notifications_use_mail_channel(): void
    {
        $user = User::factory()->create();
        
        $notifications = [
            new PurchaseConfirmationNotification([
                'purchase_id' => 1,
                'order_number' => 'ORD-123',
                'prompt_title' => 'Test',
                'price' => 9.99,
            ]),
            new PromptApprovedNotification([
                'id' => 1,
                'title' => 'Test',
                'slug' => 'test',
                'price' => 9.99,
            ]),
            new PromptRejectedNotification([
                'id' => 1,
                'title' => 'Test',
                'rejection_reason' => 'Test',
            ]),
            new SellerSaleNotification([
                'purchase_id' => 1,
                'prompt_title' => 'Test',
                'price' => 9.99,
                'seller_earnings' => 8.49,
                'order_number' => 'ORD-123',
            ]),
        ];

        foreach ($notifications as $notification) {
            $via = $notification->via($user);
            $this->assertContains('mail', $via);
        }
    }
}
