<?php

namespace App\Notifications;

use App\Models\MonthlyBudgetReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyBudgetReportRequestSign extends Notification implements ShouldQueue
{
    use Queueable;

    private $report;

    private $detail;

    /**
     * Create a new notification instance.
     */
    public function __construct(MonthlyBudgetReport $report, $detail)
    {
        $this->report = $report;
        $this->detail = $detail;
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
            ->from(env('MAIL_FROM_ADDRESS', 'pt.daijoindustrial@daijo.co.id'))
            ->cc('nur@daijo.co.id')
            ->greeting($this->detail['greeting'])
            ->line($this->detail['body'])
            // ->line('We waiting for Mr/Mrs.' . ucwords($this->detail['userName']) . ' to sign the report.')
            ->action($this->detail['actionText'], $this->detail['actionURL']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'data' => ' Monthly Budget Report of '.$this->report->id.' needs your sign',
        ];
    }
}
