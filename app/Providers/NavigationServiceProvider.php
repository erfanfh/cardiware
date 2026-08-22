<?php

namespace App\Providers;

use App\Application\Navigation\Actions\ResolveFrontendNavigationMenuAction;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawWinner;
use App\Infrastructure\Services\Setting\Groups\RaffleSettings;
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

            if (app(RaffleSettings::class)->get('show_in_header_navigation', true)) {
                $raffleUrl = route('raffles.index');
                $alreadyPresent = $items->contains(fn (array $item): bool => rtrim((string) $item['url'], '/') === rtrim($raffleUrl, '/'));

                if (! $alreadyPresent) {
                    $items->push([
                        'id' => 'system-raffles',
                        'title' => 'قرعه‌کشی‌ها',
                        'url' => $raffleUrl,
                        'target' => '_self',
                        'icon' => '🎁',
                        'badge' => null,
                        'css_class' => '',
                        'is_mega_menu' => false,
                        'mega_menu_title' => null,
                        'mega_menu_description' => null,
                        'mega_menu_columns' => null,
                        'children' => collect(),
                    ]);
                }
            }

            $raffleHeaderNotifications = collect();
            if (auth()->check()) {
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
            ]);
        });
    }
}
