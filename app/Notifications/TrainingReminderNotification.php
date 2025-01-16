<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $training;

    /**
     * Create a new notification instance.
     */
    public function __construct($training)
    {
        $this->training = $training;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Training Reminder')
                    ->greeting('Training Reminder')
                    ->line('Employee Name: ' . $this->training->employee->Nama)
                    ->line('Employee NIK: ' . $this->training->employee->NIK)
                    ->line('Training Description: ' . $this->training->description)
                    ->line('Last Training Date: ' . \Carbon\Carbon::parse($this->training->last_training_at)->format('d-m-Y'))
                    ->action('View Training Details', route('employee_trainings.show', $this->training->id))
                    ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Training reminder with has just been created!',
        ];
    }
}
