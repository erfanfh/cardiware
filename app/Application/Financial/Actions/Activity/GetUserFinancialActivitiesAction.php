<?php

namespace App\Application\Financial\Actions\Activity;

use App\Application\Financial\DTOs\FinancialActivityDTO;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\FinancialActivityDirectionEnum;
use App\Domain\Financial\Enums\FinancialActivitySourceEnum;
use App\Domain\Financial\Enums\FinancialActivityStatusEnum;
use App\Domain\Financial\Enums\FinancialActivityTypeEnum;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Enums\WalletTopUpPurposeEnum;
use App\Domain\Financial\Enums\WalletTopUpStatusEnum;
use App\Domain\Financial\Enums\WalletTransactionDirectionEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Enums\WalletTransferStatusEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\WalletTopUp;
use App\Domain\Financial\Models\WalletTransaction;
use App\Domain\Financial\Models\WalletTransfer;
use App\Domain\Identity\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class GetUserFinancialActivitiesAction
{
    /**
     * @return LengthAwarePaginator<int, FinancialActivityDTO>
     */
    public function execute(
        User                            $user,
        ?FinancialActivityTypeEnum      $type = null,
        ?FinancialActivityStatusEnum    $status = null,
        ?FinancialActivityDirectionEnum $direction = null,
        int                             $perPage = 10,
        int                             $page = 1,
    ): LengthAwarePaginator
    {
        if (!$user->exists) {
            throw new InvalidArgumentException(
                'User must exist before loading financial activities.'
            );
        }

        $perPage = max(1, min($perPage, 50));
        $page = max(1, $page);

        $unionQuery = $this->buildUnionQuery($user);

        $activitiesQuery = DB::query()
            ->fromSub(
                $unionQuery,
                'financial_activities',
            );

        if ($type) {
            $activitiesQuery->where(
                'activity_type',
                $type->value,
            );
        }

        if ($status) {
            $activitiesQuery->where(
                'activity_status',
                $status->value,
            );
        }

        if ($direction) {
            $activitiesQuery->where(
                'activity_direction',
                $direction->value,
            );
        }

        $paginator = $activitiesQuery
            ->orderByDesc('occurred_at')
            ->orderByDesc('sort_id')
            ->orderByDesc('source_id')
            ->paginate(
                perPage: $perPage,
                columns: ['*'],
                pageName: 'page',
                page: $page,
            );

        $rows = collect($paginator->items());

        $paginator->setCollection(
            $this->hydrateActivities(
                rows: $rows,
                user: $user,
            ),
        );

        return $paginator;
    }

    private function buildUnionQuery(
        User $user,
    ): Builder
    {
        $queries = [
            $this->directOrderPaymentsQuery($user),
            $this->walletTopUpsQuery($user),
            $this->walletTransfersQuery($user),
            $this->standaloneWalletTransactionsQuery($user),
        ];

        /** @var Builder $union */
        $union = array_shift($queries);

        foreach ($queries as $query) {
            $union->unionAll($query);
        }

        return $union;
    }

    /**
     * پرداخت‌های مستقیم سفارش:
     *
     * - پرداخت خارجی سفارش
     * - پرداخت کامل سفارش با کیف پول
     *
     * پرداخت داخلی مربوط به پرداخت ترکیبی از این Query حذف می‌شود.
     */
    private function directOrderPaymentsQuery(
        User $user,
    ): Builder
    {
        $orderMorphClass = (new Order())->getMorphClass();

        return DB::table('payments')
            ->join('orders', function ($join) use (
                $orderMorphClass,
            ): void {
                $join
                    ->on(
                        'orders.id',
                        '=',
                        'payments.payable_id',
                    )
                    ->where(
                        'payments.payable_type',
                        '=',
                        $orderMorphClass,
                    );
            })
            ->where(
                'orders.user_id',
                $user->getKey(),
            )
            ->where(
                'payments.amount',
                '>',
                0,
            )
            ->where(
                'payments.gateway',
                '<>',
                PaymentGatewayEnum::FREE->value,
            )
            ->whereNotExists(function (Builder $query): void {
                $query
                    ->selectRaw('1')
                    ->from('wallet_top_ups')
                    ->whereColumn(
                        'wallet_top_ups.order_payment_id',
                        'payments.id',
                    );
            })
            ->select([
                'payments.id as source_id',
                'payments.id as sort_id',
            ])
            ->selectRaw(
                '? as source',
                [
                    FinancialActivitySourceEnum::PAYMENT->value,
                ],
            )
            ->selectRaw(
                '? as activity_type',
                [
                    FinancialActivityTypeEnum::ORDER_PAYMENT->value,
                ],
            )
            ->selectRaw(
                '
                CASE payments.status
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    ELSE ?
                END as activity_status
            ',
                [
                    PaymentStatusEnum::INITIATED->value,
                    FinancialActivityStatusEnum::PENDING->value,

                    PaymentStatusEnum::PENDING->value,
                    FinancialActivityStatusEnum::PENDING->value,

                    PaymentStatusEnum::PAID->value,
                    FinancialActivityStatusEnum::SUCCESSFUL->value,

                    PaymentStatusEnum::FAILED->value,
                    FinancialActivityStatusEnum::FAILED->value,

                    PaymentStatusEnum::CANCELLED->value,
                    FinancialActivityStatusEnum::CANCELLED->value,

                    PaymentStatusEnum::EXPIRED->value,
                    FinancialActivityStatusEnum::EXPIRED->value,

                    FinancialActivityStatusEnum::FAILED->value,
                ],
            )
            ->selectRaw(
                '
                    CASE
                        WHEN payments.status = ?
                            THEN ?
                        ELSE NULL
                    END as activity_direction
                ',
                [
                    PaymentStatusEnum::PAID->value,
                    FinancialActivityDirectionEnum::DEBIT->value,
                ],
            )
            ->addSelect([
                'payments.amount as amount',
            ])
            ->selectRaw(
                '
                CASE payments.status
                    WHEN ? THEN COALESCE(
                        payments.paid_at,
                        payments.updated_at,
                        payments.created_at
                    )

                    WHEN ? THEN COALESCE(
                        payments.failed_at,
                        payments.updated_at,
                        payments.created_at
                    )

                    WHEN ? THEN COALESCE(
                        payments.failed_at,
                        payments.updated_at,
                        payments.created_at
                    )

                    WHEN ? THEN COALESCE(
                        payments.failed_at,
                        payments.updated_at,
                        payments.created_at
                    )

                    ELSE COALESCE(
                        payments.created_at,
                        payments.updated_at
                    )
                END as occurred_at
                ',
                [
                    PaymentStatusEnum::PAID->value,
                    PaymentStatusEnum::FAILED->value,
                    PaymentStatusEnum::CANCELLED->value,
                    PaymentStatusEnum::EXPIRED->value,
                ],
            );
    }

    /**
     * شارژ عادی و پرداخت ترکیبی سفارش.
     *
     * در حالت نادر credited_only_order_already_paid، مبلغ پرداخت‌شده
     * به کیف پول کاربر واریز شده و به عنوان شارژ نمایش داده می‌شود.
     */
    private function walletTopUpsQuery(
        User $user,
    ): Builder
    {
        return DB::table('wallet_top_ups')
            ->join(
                'wallets',
                'wallets.id',
                '=',
                'wallet_top_ups.wallet_id',
            )
            ->leftJoin(
                'orders',
                'orders.id',
                '=',
                'wallet_top_ups.order_id',
            )
            ->where(
                'wallets.user_id',
                $user->getKey(),
            )
            ->select([
                'wallet_top_ups.id as source_id',
                'wallet_top_ups.id as sort_id',
            ])
            ->selectRaw(
                '? as source',
                [
                    FinancialActivitySourceEnum::WALLET_TOP_UP->value,
                ],
            )
            ->selectRaw(
                '
                CASE
                    WHEN wallet_top_ups.purpose = ?
                        THEN ?

                    WHEN wallet_top_ups.purpose = ?
                        AND wallet_top_ups.status = ?
                        AND wallet_top_ups.order_payment_id IS NULL
                        THEN ?

                    ELSE ?
                END as activity_type
                ',
                [
                    WalletTopUpPurposeEnum::MANUAL->value,
                    FinancialActivityTypeEnum::WALLET_TOP_UP->value,

                    WalletTopUpPurposeEnum::ORDER_PAYMENT->value,
                    WalletTopUpStatusEnum::COMPLETED->value,
                    FinancialActivityTypeEnum::WALLET_TOP_UP->value,

                    FinancialActivityTypeEnum::ORDER_PAYMENT->value,
                ],
            )
            ->selectRaw(
                '
                CASE wallet_top_ups.status
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    ELSE ?
                END as activity_status
                ',
                [
                    WalletTopUpStatusEnum::PENDING_PAYMENT->value,
                    FinancialActivityStatusEnum::PENDING->value,

                    WalletTopUpStatusEnum::COMPLETED->value,
                    FinancialActivityStatusEnum::SUCCESSFUL->value,

                    WalletTopUpStatusEnum::FAILED->value,
                    FinancialActivityStatusEnum::FAILED->value,

                    WalletTopUpStatusEnum::CANCELLED->value,
                    FinancialActivityStatusEnum::CANCELLED->value,

                    WalletTopUpStatusEnum::EXPIRED->value,
                    FinancialActivityStatusEnum::EXPIRED->value,

                    FinancialActivityStatusEnum::FAILED->value,
                ],
            )
            ->selectRaw(
                '
                CASE
                    WHEN wallet_top_ups.status <> ?
                        THEN NULL

                    WHEN wallet_top_ups.purpose = ?
                        THEN ?

                    WHEN wallet_top_ups.purpose = ?
                        AND wallet_top_ups.order_payment_id IS NULL
                        THEN ?

                    ELSE ?
                END as activity_direction
                ',
                [
                    WalletTopUpStatusEnum::COMPLETED->value,

                    WalletTopUpPurposeEnum::MANUAL->value,
                    FinancialActivityDirectionEnum::CREDIT->value,

                    WalletTopUpPurposeEnum::ORDER_PAYMENT->value,
                    FinancialActivityDirectionEnum::CREDIT->value,

                    FinancialActivityDirectionEnum::DEBIT->value,
                ],
            )
            ->selectRaw(
                '
                CASE
                    WHEN wallet_top_ups.purpose = ?
                        THEN wallet_top_ups.amount

                    WHEN wallet_top_ups.purpose = ?
                        AND wallet_top_ups.status = ?
                        AND wallet_top_ups.order_payment_id IS NULL
                        THEN wallet_top_ups.amount

                    ELSE COALESCE(
                        orders.total_amount,
                        wallet_top_ups.amount
                    )
                END as amount
                ',
                [
                    WalletTopUpPurposeEnum::MANUAL->value,

                    WalletTopUpPurposeEnum::ORDER_PAYMENT->value,
                    WalletTopUpStatusEnum::COMPLETED->value,
                ],
            )
            ->selectRaw(
                '
                CASE wallet_top_ups.status
                    WHEN ? THEN COALESCE(
                        wallet_top_ups.completed_at,
                        wallet_top_ups.updated_at,
                        wallet_top_ups.created_at
                    )

                    WHEN ? THEN COALESCE(
                        wallet_top_ups.failed_at,
                        wallet_top_ups.updated_at,
                        wallet_top_ups.created_at
                    )

                    WHEN ? THEN COALESCE(
                        wallet_top_ups.cancelled_at,
                        wallet_top_ups.updated_at,
                        wallet_top_ups.created_at
                    )

                    WHEN ? THEN COALESCE(
                        wallet_top_ups.expires_at,
                        wallet_top_ups.updated_at,
                        wallet_top_ups.created_at
                    )

                    ELSE COALESCE(
                        wallet_top_ups.created_at,
                        wallet_top_ups.updated_at
                    )
                END as occurred_at
                ',
                [
                    WalletTopUpStatusEnum::COMPLETED->value,
                    WalletTopUpStatusEnum::FAILED->value,
                    WalletTopUpStatusEnum::CANCELLED->value,
                    WalletTopUpStatusEnum::EXPIRED->value,
                ],
            );
    }

    /**
     * انتقال‌های ارسالی و دریافتی کاربر.
     */
    private function walletTransfersQuery(
        User $user,
    ): Builder
    {
        $userId = (int)$user->getKey();

        return DB::table('wallet_transfers')
            ->where(function (Builder $query) use (
                $userId,
            ): void {
                $query
                    ->where(
                        'sender_user_id',
                        $userId,
                    )
                    ->orWhere(
                        'recipient_user_id',
                        $userId,
                    );
            })
            ->select([
                'wallet_transfers.id as source_id',
                'wallet_transfers.id as sort_id',
            ])
            ->selectRaw(
                '? as source',
                [
                    FinancialActivitySourceEnum::WALLET_TRANSFER->value,
                ],
            )
            ->selectRaw(
                '? as activity_type',
                [
                    FinancialActivityTypeEnum::WALLET_TRANSFER->value,
                ],
            )
            ->selectRaw(
                '
                CASE wallet_transfers.status
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    WHEN ? THEN ?
                    ELSE ?
                END as activity_status
                ',
                [
                    WalletTransferStatusEnum::PENDING->value,
                    FinancialActivityStatusEnum::PENDING->value,

                    WalletTransferStatusEnum::COMPLETED->value,
                    FinancialActivityStatusEnum::SUCCESSFUL->value,

                    WalletTransferStatusEnum::FAILED->value,
                    FinancialActivityStatusEnum::FAILED->value,

                    FinancialActivityStatusEnum::FAILED->value,
                ],
            )
            ->selectRaw(
                '
                CASE
                    WHEN wallet_transfers.status <> ?
                        THEN NULL

                    WHEN wallet_transfers.sender_user_id = ?
                        THEN ?

                    ELSE ?
                END as activity_direction
                ',
                [
                    WalletTransferStatusEnum::COMPLETED->value,

                    $userId,
                    FinancialActivityDirectionEnum::DEBIT->value,

                    FinancialActivityDirectionEnum::CREDIT->value,
                ],
            )
            ->addSelect([
                'wallet_transfers.amount as amount',
            ])
            ->selectRaw(
                '
                CASE wallet_transfers.status
                    WHEN ? THEN COALESCE(
                        wallet_transfers.completed_at,
                        wallet_transfers.updated_at,
                        wallet_transfers.created_at
                    )

                    WHEN ? THEN COALESCE(
                        wallet_transfers.failed_at,
                        wallet_transfers.updated_at,
                        wallet_transfers.created_at
                    )

                    ELSE COALESCE(
                        wallet_transfers.created_at,
                        wallet_transfers.updated_at
                    )
                END as occurred_at
                ',
                [
                    WalletTransferStatusEnum::COMPLETED->value,
                    WalletTransferStatusEnum::FAILED->value,
                ],
            );
    }

    /**
     * تراکنش‌هایی که موجودیت سطح بالاتری برای نمایش ندارند.
     */
    private function standaloneWalletTransactionsQuery(
        User $user,
    ): Builder
    {
        return DB::table('wallet_transactions')
            ->join(
                'wallets',
                'wallets.id',
                '=',
                'wallet_transactions.wallet_id',
            )
            ->where(
                'wallets.user_id',
                $user->getKey(),
            )
            ->whereIn(
                'wallet_transactions.type',
                [
                    WalletTransactionTypeEnum::ORDER_REFUND->value,
                    WalletTransactionTypeEnum::ADMIN_CREDIT->value,
                    WalletTransactionTypeEnum::ADMIN_DEBIT->value,
                    WalletTransactionTypeEnum::BONUS->value,
                    WalletTransactionTypeEnum::LOYALTY_CONVERSION->value,
                ],
            )
            ->select([
                'wallet_transactions.id as source_id',
                'wallet_transactions.id as sort_id',
            ])
            ->selectRaw(
                '? as source',
                [
                    FinancialActivitySourceEnum::WALLET_TRANSACTION->value,
                ],
            )
            ->selectRaw(
                'wallet_transactions.type as activity_type',
            )
            ->selectRaw(
                '
                CASE
                    WHEN wallet_transactions.type = ?
                        THEN ?
                    ELSE ?
                END as activity_status
                ',
                [
                    WalletTransactionTypeEnum::ORDER_REFUND->value,
                    FinancialActivityStatusEnum::REFUNDED->value,

                    FinancialActivityStatusEnum::SUCCESSFUL->value,
                ],
            )
            ->selectRaw(
                'wallet_transactions.direction as activity_direction',
            )
            ->addSelect([
                'wallet_transactions.amount as amount',
                'wallet_transactions.created_at as occurred_at',
            ]);
    }

    /**
     * @param Collection<int, object> $rows
     * @return Collection<int, FinancialActivityDTO>
     */
    private function hydrateActivities(
        Collection $rows,
        User       $user,
    ): Collection
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        $payments = $this->loadPayments($rows);
        $topUps = $this->loadTopUps($rows);
        $transfers = $this->loadTransfers($rows);
        $walletTransactions = $this->loadWalletTransactions($rows);

        return $rows->map(function (object $row) use (
            $user,
            $payments,
            $topUps,
            $transfers,
            $walletTransactions,
        ): FinancialActivityDTO {
            $source = FinancialActivitySourceEnum::from(
                (string)$row->source,
            );

            $sourceId = (int)$row->source_id;

            return match ($source) {
                FinancialActivitySourceEnum::PAYMENT =>
                $this->mapPayment(
                    $this->requireModel(
                        models: $payments,
                        id: $sourceId,
                        source: $source,
                    ),
                ),

                FinancialActivitySourceEnum::WALLET_TOP_UP =>
                $this->mapWalletTopUp(
                    $this->requireModel(
                        models: $topUps,
                        id: $sourceId,
                        source: $source,
                    ),
                ),

                FinancialActivitySourceEnum::WALLET_TRANSFER =>
                $this->mapWalletTransfer(
                    transfer: $this->requireModel(
                        models: $transfers,
                        id: $sourceId,
                        source: $source,
                    ),
                    user: $user,
                ),

                FinancialActivitySourceEnum::WALLET_TRANSACTION =>
                $this->mapWalletTransaction(
                    $this->requireModel(
                        models: $walletTransactions,
                        id: $sourceId,
                        source: $source,
                    ),
                ),
            };
        });
    }

    /**
     * @param Collection<int, object> $rows
     * @return Collection<int, Payment>
     */
    private function loadPayments(
        Collection $rows,
    ): Collection
    {
        $ids = $this->sourceIds(
            rows: $rows,
            source: FinancialActivitySourceEnum::PAYMENT,
        );

        if ($ids === []) {
            return collect();
        }

        return Payment::query()
            ->with('payable')
            ->whereKey($ids)
            ->get()
            ->keyBy('id');
    }

    /**
     * @param Collection<int, object> $rows
     * @return Collection<int, WalletTopUp>
     */
    private function loadTopUps(
        Collection $rows,
    ): Collection
    {
        $ids = $this->sourceIds(
            rows: $rows,
            source: FinancialActivitySourceEnum::WALLET_TOP_UP,
        );

        if ($ids === []) {
            return collect();
        }

        return WalletTopUp::query()
            ->with([
                'wallet',
                'order',
                'walletHold',
                'payment',
                'creditedTransaction',
                'orderPayment',
            ])
            ->whereKey($ids)
            ->get()
            ->keyBy('id');
    }

    /**
     * @param Collection<int, object> $rows
     * @return Collection<int, WalletTransfer>
     */
    private function loadTransfers(
        Collection $rows,
    ): Collection
    {
        $ids = $this->sourceIds(
            rows: $rows,
            source: FinancialActivitySourceEnum::WALLET_TRANSFER,
        );

        if ($ids === []) {
            return collect();
        }

        return WalletTransfer::query()
            ->with([
                'sender',
                'recipient',
                'debitTransaction',
                'creditTransaction',
            ])
            ->whereKey($ids)
            ->get()
            ->keyBy('id');
    }

    /**
     * @param Collection<int, object> $rows
     * @return Collection<int, WalletTransaction>
     */
    private function loadWalletTransactions(
        Collection $rows,
    ): Collection
    {
        $ids = $this->sourceIds(
            rows: $rows,
            source: FinancialActivitySourceEnum::WALLET_TRANSACTION,
        );

        if ($ids === []) {
            return collect();
        }

        return WalletTransaction::query()
            ->with([
                'reference',
                'actor',
            ])
            ->whereKey($ids)
            ->get()
            ->keyBy('id');
    }

    /**
     * @param Collection<int, object> $rows
     * @return array<int>
     */
    private function sourceIds(
        Collection                  $rows,
        FinancialActivitySourceEnum $source,
    ): array
    {
        return $rows
            ->where('source', $source->value)
            ->pluck('source_id')
            ->map(
                static fn(mixed $id): int => (int)$id,
            )
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @template TModel of Model
     *
     * @param Collection<int, TModel> $models
     * @return TModel
     */
    private function requireModel(
        Collection                  $models,
        int                         $id,
        FinancialActivitySourceEnum $source,
    ): Model
    {
        $model = $models->get($id);

        if (!$model instanceof Model) {
            throw new LogicException(
                "Financial activity source [{$source->value}:{$id}] could not be loaded."
            );
        }

        return $model;
    }

    private function mapPayment(
        Payment $payment,
    ): FinancialActivityDTO
    {
        $order = $payment->payable;

        if (!$order instanceof Order) {
            throw new LogicException(
                "Payment [{$payment->id}] does not belong to an order."
            );
        }

        $status = $this->mapPaymentStatus(
            $payment->status,
        );

        return new FinancialActivityDTO(
            key: 'payment:' . $payment->id,

            source: FinancialActivitySourceEnum::PAYMENT,

            sourceId: (int)$payment->id,

            type: FinancialActivityTypeEnum::ORDER_PAYMENT,

            status: $status,

            direction: $payment->isPaid()
                ? FinancialActivityDirectionEnum::DEBIT
                : null,

            title: 'پرداخت سفارش ' . $order->order_number,

            description: $this->paymentDescription($payment),

            amount: (int)$payment->amount,

            occurredAt: $this->paymentOccurredAt($payment),

            publicToken: $payment->public_token,

            trackingCode: $payment->tracking_code,

            referenceLabel: 'سفارش ' . $order->order_number,

            /*
             * نام Route صفحه جزئیات سفارش هنوز مشخص نشده است.
             */
            referenceUrl: null,

            balanceAfter: null,

            details: array_filter([
                'gateway' => $payment->gateway->label(),

                'payment_status' =>
                    $payment->status->label(),

                'gateway_reference' =>
                    $payment->gateway_reference,

                'gateway_transaction_id' =>
                    $payment->gateway_transaction_id,

                'gateway_message' =>
                    $payment->gateway_message,
            ], static fn(mixed $value): bool => filled($value)),
        );
    }

    private function mapWalletTopUp(
        WalletTopUp $topUp,
    ): FinancialActivityDTO
    {
        $status = $this->mapWalletTopUpStatus(
            $topUp->status,
        );

        $creditedOnly = $this->isCreditedOnlyOrderTopUp(
            $topUp,
        );

        $isWalletCredit = $topUp->isManual()
            || $creditedOnly;

        $orderNumber = $topUp->order?->order_number;

        $walletAmount = (int)(
            $topUp->walletHold?->amount
            ?? 0
        );

        $gatewayAmount = (int)$topUp->amount;

        $displayAmount = $isWalletCredit
            ? $gatewayAmount
            : (int)(
                $topUp->order?->total_amount
                ?? $gatewayAmount
            );

        $title = match (true) {
            $topUp->isManual() =>
            'شارژ کیف پول',

            $creditedOnly =>
            'واریز مبلغ پرداخت شده به کیف پول',

            default =>
                'پرداخت سفارش ' . ($orderNumber ?: ''),
        };

        $description = match (true) {
            $topUp->isManual() =>
            $topUp->payment
                ? 'شارژ از طریق درگاه '
                . $topUp->payment->gateway->label()
                : null,

            $creditedOnly =>
            $orderNumber
                ? "سفارش {$orderNumber} پیش از تکمیل این پرداخت، پرداخت شده بود."
                : 'مبلغ پرداخت شده به موجودی کیف پول شما افزوده شد.',

            $topUp->payment =>
                'پرداخت ترکیبی با کیف پول و درگاه '
                . $topUp->payment->gateway->label(),

            default =>
            'پرداخت ترکیبی سفارش',
        };

        return new FinancialActivityDTO(
            key: 'wallet-top-up:' . $topUp->id,

            source: FinancialActivitySourceEnum::WALLET_TOP_UP,

            sourceId: (int)$topUp->id,

            type: $isWalletCredit
                ? FinancialActivityTypeEnum::WALLET_TOP_UP
                : FinancialActivityTypeEnum::ORDER_PAYMENT,

            status: $status,

            direction: $topUp->isCompleted()
                ? (
                $isWalletCredit
                    ? FinancialActivityDirectionEnum::CREDIT
                    : FinancialActivityDirectionEnum::DEBIT
                )
                : null,

            title: trim($title),

            description: $description,

            amount: $displayAmount,

            occurredAt: $this->walletTopUpOccurredAt($topUp),

            publicToken: $topUp->public_token,

            trackingCode: $topUp->payment?->tracking_code,

            referenceLabel: $orderNumber
                ? 'سفارش ' . $orderNumber
                : null,

            referenceUrl: null,

            balanceAfter: $isWalletCredit
                ? $topUp->creditedTransaction?->balance_after
                : null,

            details: array_filter([
                'purpose' =>
                    $topUp->purpose->label(),

                'top_up_status' =>
                    $topUp->status->label(),

                'gateway' =>
                    $topUp->payment?->gateway?->label(),

                'wallet_amount' =>
                    !$isWalletCredit && $walletAmount > 0
                        ? $walletAmount
                        : null,

                'gateway_amount' =>
                    !$isWalletCredit
                        ? $gatewayAmount
                        : null,

                'withdrawable_amount' =>
                    $isWalletCredit
                    && (int)$topUp->withdrawable_amount > 0
                        ? (int)$topUp->withdrawable_amount
                        : null,

                'failure_reason' =>
                    $topUp->failure_reason,
            ], static fn(mixed $value): bool => filled($value)),
        );
    }

    private function mapWalletTransfer(
        WalletTransfer $transfer,
        User           $user,
    ): FinancialActivityDTO
    {
        $isSender =
            (int)$transfer->sender_user_id
            === (int)$user->getKey();

        $counterparty = $isSender
            ? $transfer->recipient
            : $transfer->sender;

        $counterpartyMobile = $counterparty?->mobile;

        $status = $this->mapWalletTransferStatus(
            $transfer->status,
        );

        $direction = $transfer->isCompleted()
            ? (
            $isSender
                ? FinancialActivityDirectionEnum::DEBIT
                : FinancialActivityDirectionEnum::CREDIT
            )
            : null;

        $title = $isSender
            ? 'انتقال موجودی به '
            . $this->maskMobile($counterpartyMobile)
            : 'دریافت موجودی از '
            . $this->maskMobile($counterpartyMobile);

        $walletTransaction = $isSender
            ? $transfer->debitTransaction
            : $transfer->creditTransaction;

        return new FinancialActivityDTO(
            key: 'wallet-transfer:' . $transfer->id,

            source: FinancialActivitySourceEnum::WALLET_TRANSFER,

            sourceId: (int)$transfer->id,

            type: FinancialActivityTypeEnum::WALLET_TRANSFER,

            status: $status,

            direction: $direction,

            title: $title,

            description: $transfer->description,

            amount: (int)$transfer->amount,

            occurredAt: $this->walletTransferOccurredAt($transfer),

            publicToken: $transfer->public_token,

            trackingCode: null,

            referenceLabel: null,

            referenceUrl: null,

            balanceAfter: $walletTransaction?->balance_after,

            details: array_filter([
                'counterparty_mobile' =>
                    $this->maskMobile($counterpartyMobile),

                'transfer_status' =>
                    $transfer->status->label(),

                'failure_reason' =>
                    $transfer->failure_reason,
            ], static fn(mixed $value): bool => filled($value)),
        );
    }

    private function mapWalletTransaction(
        WalletTransaction $transaction,
    ): FinancialActivityDTO
    {
        $type = FinancialActivityTypeEnum::tryFrom(
            $transaction->type->value,
        );

        if (!$type) {
            throw new LogicException(
                "Unsupported standalone wallet transaction type [{$transaction->type->value}]."
            );
        }

        $status =
            $transaction->type
            === WalletTransactionTypeEnum::ORDER_REFUND
                ? FinancialActivityStatusEnum::REFUNDED
                : FinancialActivityStatusEnum::SUCCESSFUL;

        $direction =
            $transaction->direction
            === WalletTransactionDirectionEnum::CREDIT
                ? FinancialActivityDirectionEnum::CREDIT
                : FinancialActivityDirectionEnum::DEBIT;

        $referenceLabel = match (true) {
            $transaction->reference instanceof Order =>
                'سفارش '
                . $transaction->reference->order_number,

            default => null,
        };

        return new FinancialActivityDTO(
            key: 'wallet-transaction:'
            . $transaction->id,

            source: FinancialActivitySourceEnum::WALLET_TRANSACTION,

            sourceId: (int)$transaction->id,

            type: $type,

            status: $status,

            direction: $direction,

            title: $transaction->type->label(),

            description: $transaction->description,

            amount: (int)$transaction->amount,

            occurredAt: $this->requireDate(
                $transaction->created_at,
                "Wallet transaction [{$transaction->id}] has no creation date.",
            ),

            publicToken: $transaction->public_token,

            trackingCode: null,

            referenceLabel: $referenceLabel,

            referenceUrl: null,

            balanceAfter: (int)$transaction->balance_after,

            details: array_filter([
                'withdrawable_amount' =>
                    (int)$transaction->withdrawable_amount > 0
                        ? (int)$transaction->withdrawable_amount
                        : null,
                'converted_points' =>
                    $transaction->type === WalletTransactionTypeEnum::LOYALTY_CONVERSION
                        ? number_format((int)data_get($transaction->metadata, 'converted_points')) . ' امتیاز'
                        : null,
            ], static fn(mixed $value): bool => filled($value)),
        );
    }

    private function mapPaymentStatus(
        PaymentStatusEnum $status,
    ): FinancialActivityStatusEnum
    {
        return match ($status) {
            PaymentStatusEnum::INITIATED,
            PaymentStatusEnum::PENDING =>
            FinancialActivityStatusEnum::PENDING,

            PaymentStatusEnum::PAID =>
            FinancialActivityStatusEnum::SUCCESSFUL,

            PaymentStatusEnum::FAILED =>
            FinancialActivityStatusEnum::FAILED,

            PaymentStatusEnum::CANCELLED =>
            FinancialActivityStatusEnum::CANCELLED,

            PaymentStatusEnum::EXPIRED =>
            FinancialActivityStatusEnum::EXPIRED,
        };
    }

    private function mapWalletTopUpStatus(
        WalletTopUpStatusEnum $status,
    ): FinancialActivityStatusEnum
    {
        return match ($status) {
            WalletTopUpStatusEnum::PENDING_PAYMENT =>
            FinancialActivityStatusEnum::PENDING,

            WalletTopUpStatusEnum::COMPLETED =>
            FinancialActivityStatusEnum::SUCCESSFUL,

            WalletTopUpStatusEnum::FAILED =>
            FinancialActivityStatusEnum::FAILED,

            WalletTopUpStatusEnum::CANCELLED =>
            FinancialActivityStatusEnum::CANCELLED,

            WalletTopUpStatusEnum::EXPIRED =>
            FinancialActivityStatusEnum::EXPIRED,
        };
    }

    private function mapWalletTransferStatus(
        WalletTransferStatusEnum $status,
    ): FinancialActivityStatusEnum
    {
        return match ($status) {
            WalletTransferStatusEnum::PENDING =>
            FinancialActivityStatusEnum::PENDING,

            WalletTransferStatusEnum::COMPLETED =>
            FinancialActivityStatusEnum::SUCCESSFUL,

            WalletTransferStatusEnum::FAILED =>
            FinancialActivityStatusEnum::FAILED,
        };
    }

    private function isCreditedOnlyOrderTopUp(
        WalletTopUp $topUp,
    ): bool
    {
        if (
            !$topUp->isOrderPayment()
            || !$topUp->isCompleted()
        ) {
            return false;
        }

        return data_get(
                $topUp->metadata,
                'completion_mode',
            ) === 'credited_only_order_already_paid'
            || $topUp->order_payment_id === null;
    }

    private function paymentDescription(
        Payment $payment,
    ): string
    {
        $description = 'پرداخت از طریق '
            . $payment->gateway->label();

        if (
            $payment->status->isFailed()
            && filled($payment->gateway_message)
        ) {
            return $description
                . ' ناموفق بود: '
                . $payment->gateway_message;
        }

        return $description;
    }

    private function paymentOccurredAt(
        Payment $payment,
    ): CarbonInterface
    {
        $date = match ($payment->status) {
            PaymentStatusEnum::PAID =>
                $payment->paid_at
                ?? $payment->updated_at
                ?? $payment->created_at,

            PaymentStatusEnum::FAILED,
            PaymentStatusEnum::CANCELLED,
            PaymentStatusEnum::EXPIRED =>
                $payment->failed_at
                ?? $payment->updated_at
                ?? $payment->created_at,

            PaymentStatusEnum::INITIATED,
            PaymentStatusEnum::PENDING =>
                $payment->created_at
                ?? $payment->updated_at,
        };

        return $this->requireDate(
            $date,
            "Payment [{$payment->id}] has no activity date.",
        );
    }

    private function walletTopUpOccurredAt(
        WalletTopUp $topUp,
    ): CarbonInterface
    {
        $date = match ($topUp->status) {
            WalletTopUpStatusEnum::COMPLETED =>
                $topUp->completed_at
                ?? $topUp->updated_at
                ?? $topUp->created_at,

            WalletTopUpStatusEnum::FAILED =>
                $topUp->failed_at
                ?? $topUp->updated_at
                ?? $topUp->created_at,

            WalletTopUpStatusEnum::CANCELLED =>
                $topUp->cancelled_at
                ?? $topUp->updated_at
                ?? $topUp->created_at,

            WalletTopUpStatusEnum::EXPIRED =>
                $topUp->expires_at
                ?? $topUp->updated_at
                ?? $topUp->created_at,

            WalletTopUpStatusEnum::PENDING_PAYMENT =>
                $topUp->created_at
                ?? $topUp->updated_at,
        };

        return $this->requireDate(
            $date,
            "Wallet top-up [{$topUp->id}] has no activity date.",
        );
    }

    private function walletTransferOccurredAt(
        WalletTransfer $transfer,
    ): CarbonInterface
    {
        $date = match ($transfer->status) {
            WalletTransferStatusEnum::COMPLETED =>
                $transfer->completed_at
                ?? $transfer->updated_at
                ?? $transfer->created_at,

            WalletTransferStatusEnum::FAILED =>
                $transfer->failed_at
                ?? $transfer->updated_at
                ?? $transfer->created_at,

            WalletTransferStatusEnum::PENDING =>
                $transfer->created_at
                ?? $transfer->updated_at,
        };

        return $this->requireDate(
            $date,
            "Wallet transfer [{$transfer->id}] has no activity date.",
        );
    }

    private function requireDate(
        mixed  $date,
        string $message,
    ): CarbonInterface
    {
        if (!$date instanceof CarbonInterface) {
            throw new LogicException($message);
        }

        return $date;
    }

    private function maskMobile(
        ?string $mobile,
    ): string
    {
        if (!$mobile) {
            return 'کاربر مقصد';
        }

        if (strlen($mobile) !== 11) {
            return $mobile;
        }

        return substr($mobile, 0, 4)
            . '***'
            . substr($mobile, -4);
    }
}
