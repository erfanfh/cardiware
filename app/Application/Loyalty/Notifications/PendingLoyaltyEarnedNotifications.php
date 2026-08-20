<?php

namespace App\Application\Loyalty\Notifications;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class PendingLoyaltyEarnedNotifications
{
    public const CLIENT_COOKIE = 'loyalty_earned_pending';

    private const SESSION_KEY = 'loyalty.pending_earned_notifications';

    /**
     * @param  array<string, int|string>  $notification
     */
    public function push(Request $request, array $notification): void
    {
        $pending = $request->session()->get(self::SESSION_KEY, []);
        $pending[] = $notification;

        // This queue is intentionally small and transient. The cap also prevents a
        // stale session from growing indefinitely when JavaScript is unavailable.
        $request->session()->put(self::SESSION_KEY, array_slice($pending, -5));
    }

    public function markClientPending(Request $request, Response $response): void
    {
        $response->headers->setCookie(
            Cookie::create(self::CLIENT_COOKIE)
                ->withValue('1')
                ->withExpires(now()->addMinutes(10))
                ->withPath('/')
                ->withSecure($request->isSecure())
                ->withHttpOnly(false)
                ->withSameSite(Cookie::SAMESITE_LAX),
        );
    }

    /**
     * @return list<array<string, int|string>>
     */
    public function pull(Request $request): array
    {
        $pending = $request->session()->pull(self::SESSION_KEY, []);

        return is_array($pending) ? array_values($pending) : [];
    }
}
