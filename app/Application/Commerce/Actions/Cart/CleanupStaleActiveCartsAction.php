<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Models\Cart;
use App\Infrastructure\Services\Setting\Groups\CommerceSettings;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class CleanupStaleActiveCartsAction
{
    public const DEFAULT_GUEST_RETENTION_DAYS = 10;

    public const DEFAULT_USER_RETENTION_MONTHS = 6;

    public function __construct(
        private readonly CommerceSettings $commerceSettings,
    ) {
    }

    /**
     * Delete stale ACTIVE carts using separate retention periods for guests and registered users.
     *
     * cart_items are removed automatically by the existing database cascade.
     *
     * @return array{guest: int, user: int, total: int}
     */
    public function execute(
        ?int $guestRetentionDays = null,
        ?int $userRetentionMonths = null,
    ): array {
        $guestRetentionDays = $this->normalizeGuestRetentionDays(
            $guestRetentionDays ?? $this->guestRetentionDays(),
        );

        $userRetentionMonths = $this->normalizeUserRetentionMonths(
            $userRetentionMonths ?? $this->userRetentionMonths(),
        );

        return DB::transaction(function () use ($guestRetentionDays, $userRetentionMonths): array {
            $guestDeleted = Cart::query()
                ->active()
                ->whereNull('user_id')
                ->where('updated_at', '<=', $this->guestCutoff($guestRetentionDays))
                ->delete();

            $userDeleted = Cart::query()
                ->active()
                ->whereNotNull('user_id')
                ->where('updated_at', '<=', $this->userCutoff($userRetentionMonths))
                ->delete();

            return [
                'guest' => $guestDeleted,
                'user' => $userDeleted,
                'total' => $guestDeleted + $userDeleted,
            ];
        });
    }

    public function guestRetentionDays(): int
    {
        return $this->normalizeGuestRetentionDays(
            (int) $this->commerceSettings->get(
                'abandoned_guest_cart_retention_days',
                self::DEFAULT_GUEST_RETENTION_DAYS,
            ),
        );
    }

    public function userRetentionMonths(): int
    {
        return $this->normalizeUserRetentionMonths(
            (int) $this->commerceSettings->get(
                'abandoned_user_cart_retention_months',
                self::DEFAULT_USER_RETENTION_MONTHS,
            ),
        );
    }

    public function guestCutoff(?int $retentionDays = null): CarbonInterface
    {
        $retentionDays = $this->normalizeGuestRetentionDays(
            $retentionDays ?? $this->guestRetentionDays(),
        );

        return now()->subDays($retentionDays);
    }

    public function userCutoff(?int $retentionMonths = null): CarbonInterface
    {
        $retentionMonths = $this->normalizeUserRetentionMonths(
            $retentionMonths ?? $this->userRetentionMonths(),
        );

        return now()->subMonthsNoOverflow($retentionMonths);
    }

    private function normalizeGuestRetentionDays(int $retentionDays): int
    {
        return max(1, $retentionDays);
    }

    private function normalizeUserRetentionMonths(int $retentionMonths): int
    {
        return max(1, $retentionMonths);
    }
}
