<?php

namespace App\Providers;

use App\Application\PurchaseRequest\Listeners\NotifyApprovedViewersOnApproval;
use App\Application\PurchaseRequest\Listeners\NotifyPurchasersOnApproval;
use App\Events\ApprovalCompleted;
use App\Listeners\BroadcastNotificationPushed;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
   protected $listen = [
       \App\Events\ApprovalCompleted::class => [
           \App\Application\PurchaseRequest\Listeners\HandlePurchaseRequestApprovalNotifications::class,
       ],
   ];
   

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
