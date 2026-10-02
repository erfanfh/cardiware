<?php

namespace App\Http\Controllers\Front\Identity;

use App\Application\Identity\Actions\HydrateAccountUserAction;
use App\Domain\Notification\Models\UserNotification;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index(Request $request, HydrateAccountUserAction $hydrate): View
    {
        $user = $hydrate->execute($request->user());
        $notifications = UserNotification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('sent_at')->orderByDesc('id')
            ->paginate(20);

        return view('front.account.pages.notifications', compact('user', 'notifications'));
    }

    public function read(Request $request, UserNotification $notification): RedirectResponse
    {
        $owned = UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($notification->id)
            ->firstOrFail();

        $walletPage = null;
        if ($request->input('return_to') === 'wallet') {
            $data = $request->validate(['withdrawals_page' => ['nullable', 'integer', 'min:1', 'max:100000']]);
            $walletPage = (int) ($data['withdrawals_page'] ?? 1);
        }

        UserNotification::query()->whereKey($owned->id)->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($walletPage !== null) {
            return redirect()->route('account.wallet', $walletPage > 1 ? ['withdrawals_page' => $walletPage] : [])
                ->withFragment('wallet-withdrawal-history');
        }

        $url = $owned->url;
        return redirect()->to($url && str_starts_with($url, '/') && !str_starts_with($url, '//')
            ? $url
            : route('account.notifications.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        UserNotification::query()->where('user_id', $request->user()->id)
            ->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}
