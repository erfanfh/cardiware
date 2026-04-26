<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Models\User;
use Illuminate\Support\Str;

class GenerateUniqueUsernameAction
{
    private const MAX_LENGTH = 60;

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
     * Generate a unique username.
     *
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $email
     * @param string|null $mobile
     * @return string
     */
    public function execute(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
        ?string $mobile = null,
    ): string
    {
        $baseUsername = $this->makeBaseUsername(
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            mobile: $mobile,
        );

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $username = $this->makeCandidate($baseUsername, $attempt);

            if ($this->isAvailable($username)) {
                return $username;
            }
        }

        return $this->makeFallbackUsername();
    }

    /**
     * Make base username from email, mobile, or name.
     *
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $email
     * @param string|null $mobile
     * @return string
     */
    private function makeBaseUsername(
        ?string $firstName,
        ?string $lastName,
        ?string $email,
        ?string $mobile,
    ): string
    {
        if (filled($email)) {
            $base = Str::before($email, '@');
        } elseif (filled($mobile)) {
            $base = 'user' . substr($mobile, -6);
        } else {
            $base = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
        }

        $base = Str::of($base)
            ->lower()
            ->replaceMatches('/[^a-z0-9._-]+/i', '')
            ->trim('._-')
            ->toString();

        if (blank($base) || in_array($base, self::RESERVED_USERNAMES, true)) {
            $base = 'user';
        }

        return Str::limit($base, 35, '');
    }

    /**
     * Make username candidate.
     *
     * @param string $baseUsername
     * @param int $attempt
     * @return string
     */
    private function makeCandidate(string $baseUsername, int $attempt): string
    {
        if ($attempt === 1 && !$this->isReserved($baseUsername)) {
            return $baseUsername;
        }

        $suffix = random_int(1000, 999999);

        $maxBaseLength = self::MAX_LENGTH - strlen((string)$suffix);

        return Str::limit($baseUsername, $maxBaseLength, '') . $suffix;
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
            $username = 'user' . random_int(10000000, 99999999);
        } while (!$this->isAvailable($username));

        return $username;
    }
}
