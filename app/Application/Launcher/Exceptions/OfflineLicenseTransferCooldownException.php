<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;
use Carbon\CarbonInterface;

final class OfflineLicenseTransferCooldownException extends ApiException
{
    public function __construct(CarbonInterface $availableAt)
    {
        parent::__construct(
            message: 'دستگاه جدید فعال است، اما برای جلوگیری از استفاده هم‌زمان دو دستگاه، حالت آفلاین آن پس از پایان مجوز قبلی فعال می‌شود.',
            errorCode: 'offline_license_transfer_cooldown',
            statusCode: 409,
            details: ['available_at' => $availableAt->utc()->toISOString()],
        );
    }
}
