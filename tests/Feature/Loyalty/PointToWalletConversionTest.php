<?php

namespace Tests\Feature\Loyalty;

use App\Application\Loyalty\PointConversion\Actions\ConvertPointsToWalletAction;
use App\Application\Loyalty\PointConversion\DTOs\ConvertPointsToWalletDTO;
use App\Application\Loyalty\PointConversion\Exceptions\PointConversionException;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointConversion;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PointToWalletConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_atomically_converts_points_to_non_withdrawable_wallet_credit(): void
    {
        $this->configureConversion();
        [$user, $account] = $this->userWithPoints(2_000);
        $requestKey = (string) Str::uuid();

        $conversion = app(ConvertPointsToWalletAction::class)->execute(
            new ConvertPointsToWalletDTO($user, 1_000, $requestKey),
        );

        $this->assertSame(1_000, $conversion->points);
        $this->assertSame(100_000, $conversion->wallet_amount);
        $this->assertSame(1_000, $account->refresh()->balance);
        $this->assertSame(1_000, $account->lifetime_spent);
        $this->assertSame(100_000, $conversion->wallet->refresh()->balance);
        $this->assertSame(0, $conversion->wallet->withdrawable_balance);
        $this->assertSame(
            WalletTransactionTypeEnum::LOYALTY_CONVERSION,
            $conversion->walletTransaction->type,
        );
        $this->assertSame(0, $conversion->walletTransaction->withdrawable_amount);
        $this->assertNotNull($conversion->point_ledger_entry_id);
        $this->assertNotNull($conversion->wallet_transaction_id);

        $replayed = app(ConvertPointsToWalletAction::class)->execute(
            new ConvertPointsToWalletDTO($user, 1_000, $requestKey),
        );

        $this->assertSame($conversion->getKey(), $replayed->getKey());
        $this->assertSame(1_000, $account->refresh()->balance);
        $this->assertSame(100_000, $conversion->wallet->refresh()->balance);
        $this->assertDatabaseCount('loyalty_point_conversions', 1);
        $this->assertDatabaseCount('point_ledger_entries', 1);
        $this->assertDatabaseCount('wallet_transactions', 1);
    }

    public function test_failed_conversion_does_not_change_either_ledger(): void
    {
        $this->configureConversion();
        [$user, $account] = $this->userWithPoints(600);

        try {
            app(ConvertPointsToWalletAction::class)->execute(
                new ConvertPointsToWalletDTO($user, 1_000, (string) Str::uuid()),
            );
            $this->fail('The conversion should have failed.');
        } catch (PointConversionException $exception) {
            $this->assertStringContainsString('کافی نیست', $exception->getMessage());
        }

        $this->assertSame(600, $account->refresh()->balance);
        $this->assertDatabaseCount('loyalty_point_conversions', 0);
        $this->assertDatabaseCount('point_ledger_entries', 0);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_daily_limit_is_enforced_from_completed_conversions(): void
    {
        $this->configureConversion(dailyLimit: 1_500);
        [$user, $account] = $this->userWithPoints(3_000);
        $action = app(ConvertPointsToWalletAction::class);

        $action->execute(new ConvertPointsToWalletDTO(
            $user,
            1_000,
            (string) Str::uuid(),
        ));

        $this->expectException(PointConversionException::class);
        $this->expectExceptionMessage('سقف تبدیل روزانه');

        try {
            $action->execute(new ConvertPointsToWalletDTO(
                $user,
                1_000,
                (string) Str::uuid(),
            ));
        } finally {
            $this->assertSame(2_000, $account->refresh()->balance);
            $this->assertSame(1, PointConversion::query()->count());
        }
    }

    private function configureConversion(int $dailyLimit = 20_000): void
    {
        $settings = app(LoyaltySettings::class);
        $settings->set('point_conversion_enabled', true);
        $settings->set('point_conversion_points_per_unit', 100);
        $settings->set('point_conversion_wallet_amount_per_unit', 10_000);
        $settings->set('point_conversion_minimum_points', 500);
        $settings->set('point_conversion_maximum_points_per_request', 10_000);
        $settings->set('point_conversion_daily_points_limit', $dailyLimit);
    }

    /** @return array{User, PointAccount} */
    private function userWithPoints(int $points): array
    {
        $user = User::factory()->create();
        $account = new PointAccount;
        $account->forceFill([
            'user_id' => $user->getKey(),
            'balance' => $points,
            'lifetime_earned' => $points,
            'lifetime_spent' => 0,
        ])->save();

        return [$user, $account];
    }
}
