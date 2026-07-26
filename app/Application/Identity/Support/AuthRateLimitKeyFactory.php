<?php

namespace App\Application\Identity\Support;

use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;

final class AuthRateLimitKeyFactory
{
    public function make(
        AuthRateLimitScopeEnum $scope,
        ?string                $identifier = null,
    ): string
    {
        $ip = request()->ip() ?: 'unknown';

        $identity = implode('|', [
            $scope->value,
            $ip,
            $identifier ?: 'anonymous',
        ]);

        return 'auth-flow:' . $scope->value . ':' . hash(
                'sha256',
                $identity
            );
    }
}
