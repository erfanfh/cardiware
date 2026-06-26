<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Models\User;
use Illuminate\Support\Str;

class GenerateUniqueUsernameAction
{
    private const PREFIX = 'user_';

    private const RANDOM_LENGTH = 12;

    private const RESERVED_USERNAMES = [
        'admin',
        'administrator',
        'root',
        'support',
        'system',
        'user',
        'users',
        'login',
        'register',
        'dashboard',
        'profile',
        'settings',
        'api',
    ];

    /**
     * Generate a random unique username.
     *
     * @return string
     */
    public function execute(): string
    {
        for ($attempt = 1; $attempt <= 20; $attempt++) {
            $username = $this->makeRandomUsername();

            if ($this->isAvailable($username)) {
                return $username;
            }
        }

        return $this->makeFallbackUsername();
    }

    /**
     * Make random username.
     *
     * @return string
     */
    private function makeRandomUsername(): string
    {
        return self::PREFIX . Str::lower(Str::random(self::RANDOM_LENGTH));
    }

    /**
     * Check username availability.
     *
     * @param string $username
     * @return bool
     */
    private function isAvailable(string $username): bool
    {
        return !$this->isReserved($username)
            && !User::query()->where('username', $username)->exists();
    }

    /**
     * Check reserved username.
     *
     * @param string $username
     * @return bool
     */
    private function isReserved(string $username): bool
    {
        return in_array(strtolower($username), self::RESERVED_USERNAMES, true);
    }

    /**
     * Make fallback username.
     *
     * @return string
     */
    private function makeFallbackUsername(): string
    {
        do {
            $username = self::PREFIX . random_int(1000000000, 9999999999);
        } while (!$this->isAvailable($username));

        return $username;
    }
}
