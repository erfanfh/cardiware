<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\GetDefaultRoleAction;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RuntimeException;

class RegisterUserAction
{
    /**
     * Register a new user.
     *
     * @param string $firstName
     * @param string $lastName
     * @param string|null $displayName
     * @param string $mobile
     * @param string|null $email
     * @param string $password
     * @param int|null $roleId
     * @param string|null $status
     * @param string|null $username
     * @return User
     */
    public function execute(
        string  $firstName,
        string  $lastName,
        ?string $displayName,
        string  $mobile,
        ?string $email,
        string  $password,
        ?int    $roleId = null,
        ?string $status = null,
        ?string $username = null,
    ): User
    {
        $role = $roleId
            ? Role::query()->find($roleId)
            : app(GetDefaultRoleAction::class)->execute();

        if (!$role) {
            throw new InvalidArgumentException('Default role not found.');
        }

        $isManualUsername = filled($username);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                $finalUsername = $isManualUsername
                    ? $username
                    : app(GenerateUniqueUsernameAction::class)->execute(
                        firstName: $firstName,
                        lastName: $lastName,
                        email: $email,
                        mobile: $mobile,
                    );

                return User::query()->create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'display_name' => $displayName ?: $firstName . ' ' . $lastName,
                    'mobile' => $mobile,
                    'email' => $email ?: null,
                    'username' => $finalUsername,
                    'password' => Hash::make($password),
                    'status' => $status ?? UserStatusEnum::ACTIVE,
                    'role_id' => $role->id,
                ]);
            } catch (QueryException $exception) {
                if ($isManualUsername || !$this->isDuplicateUsernameException($exception)) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException('Could not generate a unique username.');
    }

    /**
     * Determine if exception is caused by duplicate username.
     *
     * @param QueryException $exception
     * @return bool
     */
    private function isDuplicateUsernameException(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'users_username_unique')
            || str_contains($message, 'username')
            || $exception->getCode() === '23000';
    }
}
