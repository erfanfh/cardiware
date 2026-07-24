<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Exceptions\AuthRateLimitExceededException;
use App\Application\Identity\Support\AuthRateLimitKeyFactory;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use Illuminate\Support\Facades\RateLimiter;

final class ConsumeAuthRateLimitAction
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

        $maxAttempts = max(
            1,
            (int)config(
                "auth-flow.rate_limits.{$scope->value}.max_attempts",
                5
            )
        );

        $decaySeconds = max(
            1,
            (int)config(
                "auth-flow.rate_limits.{$scope->value}.decay_seconds",
                300
            )
        );

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfterSeconds = max(
                1,
                RateLimiter::availableIn($key)
            );

            throw new AuthRateLimitExceededException(
                retryAfterSeconds: $retryAfterSeconds,
                message: "تعداد درخواست‌ها بیش از حد مجاز است. لطفا {$retryAfterSeconds} ثانیه دیگر تلاش کنید.",
            );
        }

        RateLimiter::hit(
            key: $key,
            decaySeconds: $decaySeconds,
        );
    }
}
