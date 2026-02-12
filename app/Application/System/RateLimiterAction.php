<?php

namespace App\Application\System;

use Illuminate\Support\Facades\RateLimiter;

class RateLimiterAction
{
    /**
     * @param string $action
     * @param int $limit
     * @param int $decaySeconds
     * @return int|null
     */
    public function execute(string $action, int $limit = 5, int $decaySeconds = 60): ?int
    {
        $key = $action . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return RateLimiter::availableIn($key);
        }

        RateLimiter::hit($key, $decaySeconds);

        return null;
    }
}
