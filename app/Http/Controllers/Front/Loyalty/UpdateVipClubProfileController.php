<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\Support\VipClubProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/**
 * ذخیرهٔ معرفی و تم کارت باشگاه؛ فقط برای کاربران VIP.
 *
 * معرفی (club_bio) تا تایید مدیر به‌صورت عمومی نمایش داده نمی‌شود و تا وقتی
 * «در انتظار تایید» است کاربر نمی‌تواند آن یا تم را تغییر دهد. نوشتن فقط
 * یک‌بار ممکن است؛ برای ویرایش مجدد باید اشتراکِ ماهِ بعدِ آخرین ویرایش
 * خریداری شده باشد (User::canEditClubBio).
 */
final class UpdateVipClubProfileController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasActiveVipSubscription()) {
            return redirect()->to(route('account.loyalty') . '#overview')
                ->withErrors(['club_bio' => 'پروفایل اختصاصی فقط برای اعضای VIP فعال است.']);
        }

        if (! $user->canEditClubBio()) {
            return redirect()->to(route('account.loyalty') . '#overview')
                ->withErrors(['club_bio' => 'معرفی شما در انتظار تایید مدیر است و تا تعیین تکلیف نمی‌توانید آن را تغییر دهید. برای ویرایش بعدی، اشتراک ماه بعد را تهیه کنید.']);
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

        $bio = filled($data['club_bio'] ?? null)
            ? trim(preg_replace('/\s+/u', ' ', $data['club_bio']))
            : null;

        $user->forceFill([
            'club_bio' => $bio,
            'club_theme' => $data['club_theme'],
            // متن جدید همیشه باید دوباره تایید شود؛ تم فقط همراه همان متن قابل تغییر است.
            'club_bio_status' => $bio === null ? 'none' : 'pending',
            'club_bio_edited_at' => now(),
        ])->save();

        Cache::forget('loyalty:member-card:' . $user->getKey());

        $message = $bio === null
            ? 'معرفی شما حذف شد.'
            : 'معرفی شما ثبت شد و پس از تایید مدیر روی کارت عمومی شما نمایش داده می‌شود.';

        return redirect()->to(route('account.loyalty') . '#overview')
            ->with('success', $message);
    }
}
