<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

/**
 * Launcher login/API refused because «حالت تعمیر لانچر» is on (see LauncherMaintenance).
 * 503 + its own code, so launchers can show the admin's message instead of a generic error.
 */
final class LauncherMaintenanceException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct(
            message: $message,
            errorCode: 'launcher_maintenance',
            statusCode: 503,
        );
    }
}
