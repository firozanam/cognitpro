<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $purchaseData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $purchaseData)
    {
        $this->purchaseData = $purchaseData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Purchase Confirmation - Order #' . $this->purchaseData['order_number'])
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for your purchase! Your order has been confirmed.')
            ->line('**Order Details:**')
            ->line('Order Number: ' . $this->purchaseData['order_number'])
            ->line('Prompt: ' . $this->purchaseData['prompt_title'])
            ->line('Amount: $' . number_format($this->purchaseData['price'], 2))
            ->action('View Purchase', route('purchases.show', $this->purchaseData['purchase_id']))
            ->line('You now have full access to the prompt content.')
            ->line('Thank you for using CognitPro!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'purchase_id' => $this->purchaseData['purchase_id'],
            'order_number' => $this->purchaseData['order_number'],
            'prompt_title' => $this->purchaseData['prompt_title'],
            'price' => $this->purchaseData['price'],
        ];
    }
}
