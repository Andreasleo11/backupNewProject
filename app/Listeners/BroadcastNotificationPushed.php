<?php

namespace App\Listeners;

use App\Events\NotificationPushed;
use App\Models\User;

class BroadcastNotificationPushed
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        // Only when the 'database' channel is used
        if ($event->channel !== 'database') {
            return;
        }

        if ($event->notifiable instanceof User) {
            event(new NotificationPushed($event->notifiable->id));
        }
    }
}
