<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\Support\VipClubProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/** ذخیره‌ی معرفی و تم کارت باشگاه؛ فقط برای کاربران VIP. */
final class UpdateVipClubProfileController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasActiveVipSubscription()) {
            return redirect()->to(route('account.loyalty') . '#overview')
                ->withErrors(['club_bio' => 'پروفایل اختصاصی فقط برای اعضای VIP فعال است.']);
        }

        $data = $request->validate([
            'club_bio' => [
                'nullable', 'string', 'max:' . VipClubProfile::BIO_MAX,
                // لینک و شماره تماس در کارت عمومی مجاز نیست (جلوگیری از تبلیغ و اسپم).
                'not_regex:/(https?:\/\/|www\.|\.(com|ir|net|org)\b|@|0?9\d{9})/iu',
            ],
            'club_theme' => ['required', Rule::in(array_keys(VipClubProfile::THEMES))],
        ], [
            'club_bio.max' => 'معرفی حداکثر ' . VipClubProfile::BIO_MAX . ' نویسه است.',
            'club_bio.not_regex' => 'در معرفی، لینک، ایمیل یا شماره تماس مجاز نیست.',
        ]);

        $user->forceFill([
            'club_bio' => filled($data['club_bio'] ?? null) ? trim(preg_replace('/\s+/u', ' ', $data['club_bio'])) : null,
            'club_theme' => $data['club_theme'],
        ])->save();

        Cache::forget('loyalty:member-card:' . $user->getKey());

        return redirect()->to(route('account.loyalty') . '#overview')
            ->with('success', 'پروفایل VIP شما ذخیره شد.');
    }
}
