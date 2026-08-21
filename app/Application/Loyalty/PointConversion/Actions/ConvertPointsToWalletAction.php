<?php

namespace App\Application\Loyalty\PointConversion\Actions;

use App\Application\Financial\Actions\Wallet\CreditWalletAction;
use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Application\Financial\DTOs\CreditWalletDTO;
use App\Application\Loyalty\Actions\SpendPointsAction;
use App\Application\Loyalty\DTOs\SpendPointsDTO;
use App\Application\Loyalty\PointConversion\DTOs\ConvertPointsToWalletDTO;
use App\Application\Loyalty\PointConversion\DTOs\PointConversionConfig;
use App\Application\Loyalty\PointConversion\Exceptions\PointConversionException;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointConversion;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ConvertPointsToWalletAction
{
    public function __construct(
        private readonly ResolvePointConversionConfigAction $resolveConfig,
        private readonly GetOrCreateWalletAction $getOrCreateWallet,
        private readonly SpendPointsAction $spendPoints,
        private readonly CreditWalletAction $creditWallet,
    ) {}

    /** @throws Throwable */
    public function execute(ConvertPointsToWalletDTO $data): PointConversion
    {
        $requestKey = $this->requestKey($data);
        $fingerprint = $this->fingerprint($data);

        if ($existing = $this->findExisting($requestKey, $fingerprint)) {
            return $existing;
        }

        $config = $this->resolveConfig->execute();
        $this->validateRequestedPoints($data->points, $config);

        try {
            return DB::transaction(function () use (
                $data,
                $config,
                $requestKey,
                $fingerprint,
            ): PointConversion {
                $account = PointAccount::query()
                    ->where('user_id', $data->user->getKey())
                    ->lockForUpdate()
                    ->first();

                if (! $account || (int) $account->balance < $data->points) {
                    throw PointConversionException::invalidAmount(
                        'امتیاز قابل استفاده شما برای این تبدیل کافی نیست.',
                    );
                }

                // Point account is always locked before the wallet. Keeping this
                // order stable prevents cross-ledger deadlocks.
                $wallet = $this->getOrCreateWallet->execute($data->user);
                $wallet = Wallet::query()
                    ->whereKey($wallet->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $wallet->allowsUserTransactions()) {
                    throw PointConversionException::walletUnavailable();
                }

                if ($existing = $this->findExisting($requestKey, $fingerprint)) {
                    return $existing;
                }

                $this->enforceDailyLimit($data, $config, now());

                $walletAmount = $config->walletAmountFor($data->points);

                if ($walletAmount <= 0 || $walletAmount > PHP_INT_MAX - (int) $wallet->balance) {
                    throw PointConversionException::invalidAmount(
                        'مبلغ این تبدیل خارج از محدوده مجاز کیف پول است.',
                    );
                }

                $conversion = PointConversion::query()->create([
                    'user_id' => $data->user->getKey(),
                    'point_account_id' => $account->getKey(),
                    'wallet_id' => $wallet->getKey(),
                    'points' => $data->points,
                    'wallet_amount' => $walletAmount,
                    'points_per_unit' => $config->pointsPerUnit,
                    'wallet_amount_per_unit' => $config->walletAmountPerUnit,
                    'idempotency_key' => $requestKey,
                    'idempotency_fingerprint' => $fingerprint,
                ]);

                $pointEntry = $this->spendPoints->execute(new SpendPointsDTO(
                    account: $account,
                    amount: $data->points,
                    reason: 'wallet_conversion',
                    idempotencyKey: "{$requestKey}:points",
                    actor: $data->user,
                    reference: $conversion,
                    description: 'تبدیل امتیاز باشگاه به موجودی کیف پول',
                    metadata: [
                        'event_type' => 'wallet_conversion',
                        'wallet_amount' => $walletAmount,
                        'points_per_unit' => $config->pointsPerUnit,
                        'wallet_amount_per_unit' => $config->walletAmountPerUnit,
                    ],
                ));

                $walletTransaction = $this->creditWallet->execute(new CreditWalletDTO(
                    wallet: $wallet,
                    type: WalletTransactionTypeEnum::LOYALTY_CONVERSION,
                    amount: $walletAmount,
                    idempotencyKey: "{$requestKey}:wallet",
                    withdrawableAmount: 0,
                    actor: $data->user,
                    reference: $conversion,
                    description: sprintf(
                        'اعتبار حاصل از تبدیل %s امتیاز باشگاه',
                        number_format($data->points),
                    ),
                    metadata: [
                        'event_type' => 'loyalty_point_conversion',
                        'converted_points' => $data->points,
                        'points_per_unit' => $config->pointsPerUnit,
                        'wallet_amount_per_unit' => $config->walletAmountPerUnit,
                    ],
                ));

                $conversion->forceFill([
                    'point_ledger_entry_id' => $pointEntry->getKey(),
                    'wallet_transaction_id' => $walletTransaction->getKey(),
                ])->save();

                return $conversion->load([
                    'pointAccount',
                    'wallet',
                    'pointLedgerEntry',
                    'walletTransaction',
                ]);
            }, 3);
        } catch (QueryException $exception) {
            if ($existing = $this->findExisting($requestKey, $fingerprint)) {
                return $existing;
            }

            throw $exception;
        }
    }

    private function validateRequestedPoints(int $points, PointConversionConfig $config): void
    {
        if (! $config->isAvailable()) {
            throw PointConversionException::unavailable();
        }

        if ($points < $config->minimumPoints) {
            throw PointConversionException::invalidAmount(sprintf(
                'حداقل مقدار قابل تبدیل %s امتیاز است.',
                number_format($config->minimumPoints),
            ));
        }

        if ($points % $config->pointsPerUnit !== 0) {
            throw PointConversionException::invalidAmount(sprintf(
                'مقدار امتیاز باید مضربی از %s باشد.',
                number_format($config->pointsPerUnit),
            ));
        }

        if (
            $config->maximumPointsPerRequest > 0
            && $points > $config->maximumPointsPerRequest
        ) {
            throw PointConversionException::invalidAmount(sprintf(
                'حداکثر مقدار هر تبدیل %s امتیاز است.',
                number_format($config->maximumPointsPerRequest),
            ));
        }
    }

    private function enforceDailyLimit(
        ConvertPointsToWalletDTO $data,
        PointConversionConfig $config,
        CarbonInterface $now,
    ): void {
        if ($config->dailyPointsLimit <= 0) {
            return;
        }

        $convertedToday = (int) PointConversion::query()
            ->where('user_id', $data->user->getKey())
            ->whereBetween('created_at', [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ])
            ->sum('points');

        if ($convertedToday + $data->points > $config->dailyPointsLimit) {
            throw PointConversionException::dailyLimitReached();
        }
    }

    private function requestKey(ConvertPointsToWalletDTO $data): string
    {
        $idempotencyKey = trim($data->idempotencyKey);

        if ($idempotencyKey === '' || Str::length($idempotencyKey) > 80) {
            throw PointConversionException::invalidAmount('شناسه درخواست تبدیل معتبر نیست.');
        }

        return "loyalty:convert:user:{$data->user->getKey()}:request:{$idempotencyKey}";
    }

    private function fingerprint(ConvertPointsToWalletDTO $data): string
    {
        return hash('sha256', json_encode([
            'version' => 1,
            'user_id' => $data->user->getKey(),
            'points' => $data->points,
        ], JSON_THROW_ON_ERROR));
    }

    private function findExisting(string $requestKey, string $fingerprint): ?PointConversion
    {
        $conversion = PointConversion::query()
            ->where('idempotency_key', $requestKey)
            ->first();

        if (! $conversion) {
            return null;
        }

        if (! hash_equals($conversion->idempotency_fingerprint, $fingerprint)) {
            throw PointConversionException::idempotencyConflict();
        }

        return $conversion->loadMissing([
            'pointAccount',
            'wallet',
            'pointLedgerEntry',
            'walletTransaction',
        ]);
    }
}
