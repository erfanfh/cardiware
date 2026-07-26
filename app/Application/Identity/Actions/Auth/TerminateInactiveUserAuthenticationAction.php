<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class TerminateInactiveUserAuthenticationAction
{
    public function execute(
        Request $request,
        User    $user,
    ): void
    {
        /*
         * همه توکن‌های Sanctum کاربر هم باطل می‌شوند.
         *
         * این Query فقط زمانی اجرا می‌شود که کاربر غیرفعال باشد.
         */
        $user->tokens()->delete();

        /*
         * خروج از Session وب
         */
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        /*
         * جلوگیری از Session Fixation و استفاده مجدد
         * از Session قبلی
         */
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
