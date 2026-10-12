<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

/**
 * The launcher sending this request is older than a release the admin marked mandatory
 * (or than minimum_supported_version). See LauncherMandatoryUpdate.
 * 426 + its own code; launchers that do not know the code still show the message.
 */
final class LauncherUpdateRequiredException extends ApiException
{
    public function __construct(string $requiredVersion)
    {
        parent::__construct(
            message: "نسخه {$requiredVersion} لانچر الزامی است. لانچر را کامل ببندید (از کنار ساعت ویندوز هم خارج شوید) و دوباره باز کنید تا آپدیت نصب شود.",
            errorCode: 'launcher_update_required',
            statusCode: 426,
        );
    }
}
