<?php

namespace App\Console\Commands;

use App\Application\Loyalty\Actions\ReverseEarnedPointsForReferenceAction;
use App\Application\Loyalty\Earning\Actions\ProcessEarningRuleAction;
use App\Application\Loyalty\Earning\Contexts\CommentEarningContext;
use App\Application\Loyalty\Earning\Contexts\PurchaseEarningContext;
use App\Application\Loyalty\Earning\Contexts\RegistrationEarningContext;
use App\Domain\Commerce\Models\Order;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Loyalty\Models\RegistrationPointEligibility;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class ReconcileCoreLoyaltyPointsCommand extends Command
{
    protected $signature = 'loyalty:reconcile-core-points
                            {--hours=24 : Look back window based on entity creation/update time}
                            {--limit=5000 : Maximum records to inspect per entity type}';

    protected $description = 'Reconcile registration, purchase and comment loyalty awards/reversals after transient event failures.';

    public function handle(
        ProcessEarningRuleAction $process,
        ReverseEarnedPointsForReferenceAction $reverse,
    ): int {
        $hours = max(1, min(24 * 30, (int) $this->option('hours')));
        $limit = max(1, min(100000, (int) $this->option('limit')));
        $timezone = (string) config('app.timezone');
        $from = now($timezone)->subHours($hours)->toImmutable();

        $stats = [
            'users' => 0,
            'orders' => 0,
            'comments' => 0,
            'awarded' => 0,
            'reversed' => 0,
            'failed' => 0,
        ];

        $this->reconcileRegistrations($process, $from, $limit, $stats);
        $this->reconcileOrders($process, $reverse, $from, $limit, $stats);
        $this->reconcileComments($process, $reverse, $from, $limit, $stats);

        $this->components->info(sprintf(
            'Reconciled users=%d, orders=%d, comments=%d, awarded=%d, reversals=%d, failed=%d.',
            $stats['users'],
            $stats['orders'],
            $stats['comments'],
            $stats['awarded'],
            $stats['reversed'],
            $stats['failed'],
        ));

        return $stats['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function reconcileRegistrations(
        ProcessEarningRuleAction $process,
        CarbonImmutable $from,
        int $limit,
        array &$stats,
    ): void {
        $remaining = $limit;

        RegistrationPointEligibility::query()
            ->with('user')
            ->where('occurred_at', '>=', $from)
            ->orderBy('id')
            ->chunkById(500, function ($eligibilities) use ($process, &$remaining, &$stats): bool {
                foreach ($eligibilities as $eligibility) {
                    if ($remaining-- <= 0) {
                        return false;
                    }

                    $stats['users']++;
                    $user = $eligibility->user;
                    if (!$user) {
                        continue;
                    }

                    try {
                        $result = $process->execute(
                            EarningRuleTypeEnum::REGISTRATION,
                            new RegistrationEarningContext(
                                user: $user,
                                occurredAt: $eligibility->occurred_at,
                            ),
                        );

                        if ($result->wasAwarded()) {
                            $stats['awarded']++;
                        }
                    } catch (Throwable $exception) {
                        $stats['failed']++;
                        report($exception);
                    }
                }

                return $remaining > 0;
            });
    }

    private function reconcileOrders(
        ProcessEarningRuleAction $process,
        ReverseEarnedPointsForReferenceAction $reverse,
        CarbonImmutable $from,
        int $limit,
        array &$stats,
    ): void {
        $remaining = $limit;

        Order::query()
            ->with('user')
            ->where('updated_at', '>=', $from)
            ->orderBy('id')
            ->chunkById(500, function ($orders) use ($process, $reverse, &$remaining, &$stats): bool {
                foreach ($orders as $order) {
                    if ($remaining-- <= 0) {
                        return false;
                    }

                    $stats['orders']++;
                    if (!$order->user) {
                        continue;
                    }

                    try {
                        if ($order->isPaid()) {
                            $purchaseLedgerState = $this->purchaseLedgerState($order);

                            if ($purchaseLedgerState['has_active_earn']) {
                                continue;
                            }

                            // Historical repair: older code could keep the first paid_at after
                            // PAID -> revoked -> PAID. If every previous purchase earn has
                            // already been reversed, updated_at is the best persisted occurrence
                            // for the current paid state and deliberately creates a fresh
                            // idempotency occurrence instead of reusing the reversed one.
                            $occurredAt = $purchaseLedgerState['has_historical_earn']
                                ? ($order->updated_at ?? $order->paid_at ?? now())
                                : ($order->paid_at ?? $order->updated_at ?? now());

                            $result = $process->execute(
                                EarningRuleTypeEnum::PURCHASE,
                                new PurchaseEarningContext(
                                    user: $order->user,
                                    order: $order,
                                    occurredAt: $occurredAt,
                                ),
                            );

                            if ($result->wasAwarded()) {
                                $stats['awarded']++;
                            }

                            continue;
                        }

                        $occurredAt = ($order->updated_at ?? now())->toImmutable();
                        $token = $occurredAt->utc()->format('YmdHis.u');
                        $reversals = $reverse->execute(
                            user: $order->user,
                            reference: $order,
                            reason: EarningRuleTypeEnum::PURCHASE->value,
                            eventKey: "loyalty:reconcile:reverse:purchase:{$order->getKey()}:{$token}",
                            occurredAt: $occurredAt,
                            metadata: [
                                'event_type' => 'loyalty_reconciliation',
                                'order_status' => $order->status->value,
                            ],
                            description: "تطبیق خودکار امتیاز سفارش {$order->order_number}",
                        );
                        $stats['reversed'] += $reversals->count();
                    } catch (Throwable $exception) {
                        $stats['failed']++;
                        report($exception);
                    }
                }

                return $remaining > 0;
            });
    }

    /** @return array{has_historical_earn:bool,has_active_earn:bool} */
    private function purchaseLedgerState(Order $order): array
    {
        $accountId = $order->user?->pointAccount()->value('id');
        if (!$accountId) {
            return [
                'has_historical_earn' => false,
                'has_active_earn' => false,
            ];
        }

        $entries = PointLedgerEntry::query()
            ->where('point_account_id', $accountId)
            ->where('type', PointEntryTypeEnum::EARN)
            ->where('reason', EarningRuleTypeEnum::PURCHASE->value)
            ->where('reference_type', $order->getMorphClass())
            ->where('reference_id', $order->getKey())
            ->withSum('reversals', 'amount')
            ->get(['id', 'amount']);

        if ($entries->isEmpty()) {
            return [
                'has_historical_earn' => false,
                'has_active_earn' => false,
            ];
        }

        $hasActiveEarn = $entries->contains(
            fn(PointLedgerEntry $entry): bool => (int) $entry->amount > (int) ($entry->reversals_sum_amount ?? 0)
        );

        return [
            'has_historical_earn' => true,
            'has_active_earn' => $hasActiveEarn,
        ];
    }

    private function reconcileComments(
        ProcessEarningRuleAction $process,
        ReverseEarnedPointsForReferenceAction $reverse,
        CarbonImmutable $from,
        int $limit,
        array &$stats,
    ): void {
        $remaining = $limit;

        Comment::query()
            ->withTrashed()
            ->with('user')
            ->whereNotNull('user_id')
            ->where('updated_at', '>=', $from)
            ->orderBy('id')
            ->chunkById(500, function ($comments) use ($process, $reverse, &$remaining, &$stats): bool {
                foreach ($comments as $comment) {
                    if ($remaining-- <= 0) {
                        return false;
                    }

                    $stats['comments']++;
                    if (!$comment->user) {
                        continue;
                    }

                    try {
                        if ($comment->isApproved() && !$comment->trashed()) {
                            $result = $process->execute(
                                EarningRuleTypeEnum::COMMENT,
                                new CommentEarningContext(
                                    user: $comment->user,
                                    comment: $comment,
                                    occurredAt: $comment->approved_at ?? $comment->updated_at,
                                ),
                            );

                            if ($result->wasAwarded()) {
                                $stats['awarded']++;
                            }

                            continue;
                        }

                        $occurredAt = ($comment->updated_at ?? $comment->deleted_at ?? now())->toImmutable();
                        $token = $occurredAt->utc()->format('YmdHis.u');
                        $reversals = $reverse->execute(
                            user: $comment->user,
                            reference: $comment,
                            reason: EarningRuleTypeEnum::COMMENT->value,
                            eventKey: "loyalty:reconcile:reverse:comment:{$comment->getKey()}:{$token}",
                            occurredAt: $occurredAt,
                            metadata: [
                                'event_type' => 'loyalty_reconciliation',
                                'comment_status' => $comment->status->value,
                                'is_deleted' => $comment->trashed(),
                            ],
                            description: 'تطبیق خودکار امتیاز دیدگاه',
                        );
                        $stats['reversed'] += $reversals->count();
                    } catch (Throwable $exception) {
                        $stats['failed']++;
                        report($exception);
                    }
                }

                return $remaining > 0;
            });
    }
}
