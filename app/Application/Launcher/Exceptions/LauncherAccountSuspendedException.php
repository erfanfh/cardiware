<?php

namespace App\Application\Launcher\Exceptions;

use App\Domain\Identity\Models\User;
use App\Infrastructure\Exceptions\ApiException;

final class LauncherAccountSuspendedException extends ApiException
{
    public function __construct(User $user)
    {
        $reason = trim((string)$user->launcher_suspension_reason);
        $permanent = $user->launcher_suspended_until === null;
        $period = $permanent
            ? 'این محرومیت دائمی است.'
            : 'زمان پایان محرومیت: ' . $user->launcher_suspended_until->format('Y/m/d H:i');
        $reasonText = $reason !== '' ? " علت: {$reason}" : '';

        parent::__construct(
            message: "حساب شما به دلیل رعایت‌نکردن قوانین یا تخلف از دسترسی به لانچر محروم شده است.{$reasonText} {$period}",
            errorCode: 'launcher_account_suspended',
            statusCode: 403,
            details: [
                'reason' => $reason !== '' ? $reason : null,
                'permanent' => $permanent,
                'suspended_until' => $user->launcher_suspended_until?->toISOString(),
            ],
        );
    }
}
