<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

/**
 * Device transfer refused while emergency-offline mode is on (see LauncherEmergencyOffline).
 */
final class DeviceTransferPausedException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message: 'در حال حاضر به دلیل شرایط اضطراری، انتقال حساب به دستگاه جدید موقتاً غیرفعال است. '
                . 'لطفاً از همان دستگاه قبلی استفاده کنید یا بعداً دوباره تلاش کنید.',
            errorCode: 'device_transfer_paused',
            statusCode: 409,
        );
    }
}
