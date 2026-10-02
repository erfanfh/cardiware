<?php

namespace App\Providers;

use App\Application\Navigation\Actions\ResolveFrontendNavigationMenuAction;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawWinner;
use App\Domain\Notification\Models\UserNotification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NavigationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('front.header.index', function ($view) {
            $items = app(ResolveFrontendNavigationMenuAction::class)->execute('header');

            $raffleHeaderNotifications = collect();
            $personalHeaderNotifications = collect();
            $personalUnreadCount = 0;
            if (auth()->check()) {
                $personalUnreadCount = UserNotification::query()->where('user_id', auth()->id())->whereNull('read_at')->count();
                $personalHeaderNotifications = UserNotification::query()
                    ->where('user_id', auth()->id())
                    ->orderByDesc('sent_at')->orderByDesc('id')->limit(5)->get()
                    ->map(fn (UserNotification $notification): array => [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'subtitle' => $notification->body,
                        'read_at' => $notification->read_at,
                        'url' => $notification->url,
                    ]);
                $raffleHeaderNotifications = DrawWinner::query()
                    ->where('user_id', auth()->id())
                    ->where('status', DrawWinnerStatusEnum::PENDING->value)
                    ->where(function ($query): void {
                        $query->where('kind', 'primary')->orWhereNotNull('promoted_at');
                    })
                    ->with(['campaign:id,public_token,title,slug', 'prize:id,title'])
                    ->latest('selected_at')
                    ->limit(5)
                    ->get()
                    ->map(fn (DrawWinner $winner): array => [
                        'title' => 'برنده قرعه‌کشی شدی: '.($winner->prize?->title ?: 'جایزه'),
                        'subtitle' => $winner->confirmation_deadline_at
                            ? 'مهلت تأیید تا '.jalali_date($winner->confirmation_deadline_at, 'Y/m/d H:i')
                            : $winner->campaign?->title,
                        'url' => route('account.raffles').'#my-wins',
                    ]);
            }

            $view->with([
                'headerNavigationItems' => $items,
                'raffleHeaderNotifications' => $raffleHeaderNotifications,
                'personalHeaderNotifications' => $personalHeaderNotifications,
                'personalUnreadCount' => $personalUnreadCount,
            ]);
        });
    }
}
