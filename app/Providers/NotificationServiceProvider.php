<?php

namespace App\Providers;

use App\Application\Commerce\Listeners\QueueOrderReceipt;
use App\Application\Notification\Listeners\QueueCustomerSmsNotifications;
use App\Application\Notification\SendInAppNotification;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Discussion\Events\CommentApprovedEvent;
use App\Domain\Discussion\Events\CommentCreatedEvent;
use App\Domain\Financial\Events\WalletWithdrawalResolvedEvent;
use App\Domain\Notification\Models\UserNotification;
use App\Domain\Support\Events\TicketRepliedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(OrderPaidEvent::class, [QueueCustomerSmsNotifications::class, 'orderPaid']);
        Event::listen(OrderPaidEvent::class, QueueOrderReceipt::class);
        Event::listen(TicketRepliedEvent::class, [QueueCustomerSmsNotifications::class, 'ticketReplied']);

        View::composer('admin.partials.topbar', function ($view): void {
            $userId = auth()->id();
            $notifications = UserNotification::query()
                ->where('user_id', $userId)
                ->orderByDesc('sent_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get();

            $view->with([
                'adminHeaderNotifications' => $notifications,
                'adminHeaderUnreadCount' => UserNotification::query()
                    ->where('user_id', $userId)
                    ->whereNull('read_at')
                    ->count(),
            ]);
        });

        Event::listen(CommentCreatedEvent::class, fn (CommentCreatedEvent $event) => app(SendInAppNotification::class)->commentCreated($event->commentId));
        Event::listen(CommentApprovedEvent::class, function (CommentApprovedEvent $event): void {
            if (! $event->approvedOnCreation) {
                app(SendInAppNotification::class)->commentApproved($event->commentId);
            }
        });
        Event::listen(WalletWithdrawalResolvedEvent::class, fn (WalletWithdrawalResolvedEvent $event) => app(SendInAppNotification::class)->withdrawalResolved($event->withdrawalId));
    }
}
