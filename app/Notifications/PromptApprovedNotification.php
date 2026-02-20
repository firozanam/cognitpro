<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromptApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $promptData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $promptData)
    {
        $this->promptData = $promptData;
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
            ->subject('Your Prompt Has Been Approved!')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Great news! Your prompt has been approved and is now live on CognitPro.')
            ->line('**Prompt Details:**')
            ->line('Title: ' . $this->promptData['title'])
            ->line('Price: $' . number_format($this->promptData['price'], 2))
            ->action('View Prompt', route('prompts.show', $this->promptData['slug']))
            ->line('Your prompt is now visible to buyers worldwide. Good luck with your sales!')
            ->line('Thank you for being part of CognitPro!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'prompt_id' => $this->promptData['id'],
            'prompt_title' => $this->promptData['title'],
        ];
    }
}
