<?php

namespace App\Http\Controllers\Front\Identity;

use App\Application\Content\Actions\GetYesterdayTopPostsAction;
use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Application\Identity\Actions\HydrateAccountUserAction;
use App\Application\Loyalty\Actions\GetAudienceClubOverviewAction;
use App\Application\Loyalty\Actions\GetAudienceClubDashboardCardAction;
use App\Domain\Financial\Models\WalletWithdrawal;
use App\Domain\Identity\Models\User;
use App\Domain\Notification\Models\UserNotification;
use App\Domain\Notification\NotificationEvents;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private readonly HydrateAccountUserAction $hydrateAccountUserAction,
    )
    {
    }

    public function dashboard(
        GetYesterdayTopPostsAction $getYesterdayTopPostsAction,
        GetAudienceClubDashboardCardAction $getAudienceClubDashboardCardAction,
    ): View
    {
        $user = $this->getHydratedUser();

        $yesterdayTopPosts = $getYesterdayTopPostsAction->execute();
        $audienceClub = $getAudienceClubDashboardCardAction->execute($user);

        return view('front.account.pages.dashboard', compact(
            'user',
            'yesterdayTopPosts',
            'audienceClub',
        ));
    }

    public function downloads(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.downloads', compact('user'));
    }

    public function orders(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.orders', compact('user'));
    }

    public function tickets(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.tickets', compact('user'));
    }

    public function favorites(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.favorites', compact('user'));
    }

    public function transactions(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.transactions', compact('user'));
    }

    public function information(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.information', compact('user'));
    }

    public function security(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.security', compact('user'));
    }

    public function wallet(): View
    {
        $user = $this->getHydratedUser();

        $wallet = app(GetOrCreateWalletAction::class)->execute($user);
        $withdrawals = WalletWithdrawal::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(5, ['*'], 'withdrawals_page')
            ->fragment('wallet-withdrawal-history');

        $withdrawalNotificationIds = UserNotification::query()
            ->where('user_id', $user->id)
            ->where('event_key', NotificationEvents::WITHDRAWAL_RESOLVED)
            ->where('subject_type', (new WalletWithdrawal)->getMorphClass())
            ->whereIn('subject_id', $withdrawals->getCollection()->pluck('id'))
            ->whereNull('read_at')
            ->pluck('id', 'subject_id');

        return view('front.account.pages.wallet', compact('user', 'wallet', 'withdrawals', 'withdrawalNotificationIds'));
    }

    public function loyalty(
        GetAudienceClubOverviewAction $getAudienceClubOverviewAction,
        \App\Application\Affiliate\Actions\EnsureUserAffiliateAction $ensureUserAffiliateAction,
        \App\Infrastructure\Services\Setting\Groups\AffiliateSettings $affiliateSettings,
    ): View
    {
        $user = $this->getHydratedUser();
        $club = $getAudienceClubOverviewAction->execute($user);

        // کد دعوت/همکاری کاربر برای تب «دعوت دوستان» به‌صورت تنبل ساخته می‌شود
        $affiliate = $affiliateSettings->isEnabled()
            ? $ensureUserAffiliateAction->execute($user)
            : $user->affiliateRecord()->first();

        return view('front.account.pages.loyalty', compact('user', 'club', 'affiliate'));
    }

    private function getHydratedUser(): User
    {
        $user = $this->getUser();

        return $this->hydrateAccountUserAction->execute($user);
    }

    private function getUser(): User
    {
        return auth()->user();
    }
}
