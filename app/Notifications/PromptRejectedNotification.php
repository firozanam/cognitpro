<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromptRejectedNotification extends Notification implements ShouldQueue
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
        $mail = (new MailMessage)
            ->subject('Your Prompt Needs Revision')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your prompt submission requires some changes before it can be approved.')
            ->line('**Prompt Details:**')
            ->line('Title: ' . $this->promptData['title']);

        if (!empty($this->promptData['rejection_reason'])) {
            $mail->line('**Reason:**')
                 ->line($this->promptData['rejection_reason']);
        }

        return $mail
            ->action('Edit Prompt', route('prompts.edit', $this->promptData['id']))
            ->line('Please make the necessary changes and resubmit for review.')
            ->line('If you have any questions, please contact our support team.')
            ->line('Thank you for your understanding.');
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
            'rejection_reason' => $this->promptData['rejection_reason'] ?? null,
        ];
    }
}
