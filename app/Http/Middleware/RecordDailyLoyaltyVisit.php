<?php

namespace App\Http\Middleware;

use App\Application\Loyalty\Earning\Actions\ProcessEarningRuleAction;
use App\Application\Loyalty\Earning\Contexts\DailyVisitEarningContext;
use App\Application\Loyalty\Earning\Enums\EarningRuleSkipReasonEnum;
use App\Application\Loyalty\Notifications\LoyaltyEarnedNotificationFactory;
use App\Application\Loyalty\Notifications\PendingLoyaltyEarnedNotifications;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordDailyLoyaltyVisit
{
    public function __construct(
        private readonly ProcessEarningRuleAction $processEarningRuleAction,
        private readonly LoyaltyEarnedNotificationFactory $notificationFactory,
        private readonly PendingLoyaltyEarnedNotifications $pendingNotifications,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldRecord($request, $response)) {
            return $response;
        }

        $user = $request->user();
        $occurredAt = now(config('app.timezone'))->toImmutable();
        $date = $occurredAt->format('Y-m-d');
        $doneKey = "loyalty:daily-visit:done:user:{$user->getKey()}:{$date}";
        $retryKey = "loyalty:daily-visit:retry:user:{$user->getKey()}:{$date}";

        if (Cache::has($doneKey) || Cache::has($retryKey)) {
            return $response;
        }

        $lock = Cache::lock(
            "loyalty:daily-visit:lock:user:{$user->getKey()}:{$date}",
            10,
        );

        if (! $lock->get()) {
            return $response;
        }

        try {
            if (Cache::has($doneKey)) {
                return $response;
            }

            $result = $this->processEarningRuleAction->execute(
                EarningRuleTypeEnum::DAILY_VISIT,
                new DailyVisitEarningContext(
                    user: $user,
                    occurredAt: $occurredAt,
                ),
            );

            if ($result->wasNewlyAwarded()) {
                $this->pendingNotifications->push(
                    $request,
                    $this->notificationFactory->forDailyVisit($result->entry),
                );
                $this->pendingNotifications->markClientPending($request, $response);
            }

            if (
                $result->evaluation->skipReason
                === EarningRuleSkipReasonEnum::NO_ACTIVE_RULE
            ) {
                Cache::put(
                    $retryKey,
                    true,
                    $occurredAt->addMinutes(15),
                );

                return $response;
            }

            Cache::put(
                $doneKey,
                true,
                $occurredAt->endOfDay()->addMinute(),
            );
        } catch (Throwable $exception) {
            report($exception);
        } finally {
            $lock->release();
        }

        return $response;
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        if (! $request->user()) {
            return false;
        }

        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }

        return $response->getStatusCode() < 500;
    }
}
