<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Support\AuthRateLimitKeyFactory;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use Illuminate\Support\Facades\RateLimiter;

final class ClearAuthRateLimitAction
{
    public function __construct(
        private readonly AuthRateLimitKeyFactory $authRateLimitKeyFactory,
    )
    {
    }

    public function execute(
        AuthRateLimitScopeEnum $scope,
        ?string                $identifier = null,
    ): void
    {
        $key = $this->authRateLimitKeyFactory->make(
            scope: $scope,
            identifier: $identifier,
        );

        RateLimiter::clear($key);
    }
}
