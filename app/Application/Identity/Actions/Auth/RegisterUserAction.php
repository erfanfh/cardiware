<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Role\ResolveRegistrationRoleAction;
use App\Application\Identity\Actions\User\CreateUserAction;
use App\Application\Identity\DTOs\RegisterUserDTO;
use App\Domain\Identity\Models\User;
use Illuminate\Database\QueryException;
use RuntimeException;

final class RegisterUserAction
{
    private const MAX_USERNAME_GENERATION_ATTEMPTS = 5;

    public function __construct(
        private readonly ResolveRegistrationRoleAction $resolveRegistrationRoleAction,
        private readonly GenerateUniqueUsernameAction  $generateUniqueUsernameAction,
        private readonly CreateUserAction              $createUserAction,
    )
    {
    }

    public function execute(RegisterUserDTO $data): User
    {
        $role = $this->resolveRegistrationRoleAction->execute(
            $data->roleId
        );

        /*
         * وقتی username به صورت دستی ارسال شده، فقط یک مرتبه
         * عملیات ساخت کاربر اجرا می‌شود.
         */
        if (filled($data->username)) {
            return $this->createUserAction->execute(
                data: $data,
                role: $role,
                username: trim($data->username),
            );
        }

        /*
         * برای username خودکار، در صورت برخورد همزمان دو درخواست
         * و ایجاد username تکراری، دوباره username تولید می‌کنیم.
         */
        for (
            $attempt = 1;
            $attempt <= self::MAX_USERNAME_GENERATION_ATTEMPTS;
            $attempt++
        ) {
            $username = $this
                ->generateUniqueUsernameAction
                ->execute();

            try {
                return $this->createUserAction->execute(
                    data: $data,
                    role: $role,
                    username: $username,
                );
            } catch (QueryException $exception) {
                if (!$this->isDuplicateUsernameException($exception)) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException(
            'پس از چند تلاش امکان تولید نام کاربری یکتا وجود نداشت.'
        );
    }

    private function isDuplicateUsernameException(
        QueryException $exception,
    ): bool
    {
        $message = strtolower(
            $exception->getMessage()
        );

        return str_contains(
            $message,
            'users_username_unique'
        );
    }
}
