<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\ClubFollow;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** دنبال‌کردن/لغو دنبال‌کردن یک عضو باشگاه (هر کاربر هر عضو را یک‌بار دنبال می‌کند). */
final class ToggleMemberFollowController extends Controller
{
    public function __invoke(User $user): RedirectResponse
    {
        $follower = auth()->user();

        abort_unless($user->status === UserStatusEnum::ACTIVE, 404);
        abort_if($follower->is($user), 403, 'نمی‌توانید خودتان را دنبال کنید.');

        $message = DB::transaction(function () use ($follower, $user): string {
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $existing = ClubFollow::query()
                ->where('follower_id', $follower->getKey())
                ->where('target_id', $user->getKey())
                ->first();

            if ($existing !== null) {
                $existing->delete();

                return 'دنبال‌کردن '.($user->display_name ?: 'این عضو').' لغو شد.';
            }

            ClubFollow::query()->create([
                'follower_id' => $follower->getKey(),
                'target_id' => $user->getKey(),
            ]);

            return 'اکنون '.($user->display_name ?: 'این عضو').' را دنبال می‌کنید.';
        });

        Cache::forget('loyalty:member-card:'.$user->getKey());

        return redirect()
            ->route('loyalty.members.show', $user)
            ->with('success', $message);
    }
}
