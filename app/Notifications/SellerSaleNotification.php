<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerSaleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $saleData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $saleData)
    {
        $this->saleData = $saleData;
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
            ->subject('You Made a Sale! 🎉')
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('Great news! Someone just purchased your prompt.')
            ->line('**Sale Details:**')
            ->line('Prompt: ' . $this->saleData['prompt_title'])
            ->line('Sale Price: $' . number_format($this->saleData['price'], 2))
            ->line('Your Earnings: $' . number_format($this->saleData['seller_earnings'], 2))
            ->line('Order Number: ' . $this->saleData['order_number'])
            ->action('View Sales', route('seller.sales'))
            ->line('Keep up the great work! Your earnings will be available for payout once they reach the minimum threshold.')
            ->line('Thank you for selling on CognitPro!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'purchase_id' => $this->saleData['purchase_id'],
            'prompt_title' => $this->saleData['prompt_title'],
            'price' => $this->saleData['price'],
            'seller_earnings' => $this->saleData['seller_earnings'],
        ];
    }
}
