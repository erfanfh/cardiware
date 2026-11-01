<?php

namespace App\Http\Middleware;

use App\Application\Affiliate\Listeners\BindReferredUserListener;
use App\Domain\Affiliate\Models\Affiliate;
use App\Infrastructure\Services\Setting\Groups\AffiliateSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * ثبت کلیک و کوکی انتساب برای لینک‌های ?ref=CODE.
 * کلیک فقط برای لینک‌هایی که پارامتر ref دارند ثبت می‌شود تا
 * بازدیدهای معمولی سایت کلیک حساب نشوند.
 */
class TrackAffiliateReferral
{
    public function __construct(
        private readonly AffiliateSettings $settings,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $code = $request->query('ref');

            if (! is_string($code) || mb_strlen($code) < 3 || mb_strlen($code) > 64) {
                return $response;
            }

            if (! $this->settings->isEnabled()) {
                return $response;
            }

            $affiliate = Affiliate::query()
                ->eligible()
                ->whereRaw('UPPER(code) = ?', [mb_strtoupper(trim($code))])
                ->first();

            if ($affiliate === null) {
                return $response;
            }

            $minutes = max(1, $affiliate->cookie_days) * 24 * 60;
            $response->headers->setCookie(
                new \Symfony\Component\HttpFoundation\Cookie(
                    BindReferredUserListener::COOKIE_NAME,
                    (string) $affiliate->getKey(),
                    time() + $minutes * 60,
                    '/',
                    null,
                    false,
                    true,
                    false,
                    'Lax',
                ),
            );

            $affiliate->clicks()->create([
                'user_id' => $request->user()?->getKey(),
                'landing_path' => mb_substr($request->path(), 0, 500),
                'ip_hash' => hash('sha256', $request->ip() . '|' . $request->userAgent()),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // کلیک‌های همکاری هرگز نباید بارگذاری صفحه را خراب کنند.
        }

        return $response;
    }
}
