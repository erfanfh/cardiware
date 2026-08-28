<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Models\User;
use DomainException;
use Illuminate\Support\Facades\Auth;

class LoginUserByIdentifierAction
{
    public function execute(string $identifier, string $password): User
    {
        $identifier = trim($identifier);

        if ($identifier === '' || $password === '') {
            throw new DomainException('Invalid login credentials.');
        }

        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'mobile';

        $value = $field === 'mobile'
            ? normalize_number($identifier)
            : mb_strtolower($identifier);

        if (!Auth::attempt([
            $field => $value,
            'password' => $password,
        ], true)) {
            throw new DomainException('Invalid login credentials.');
        }

        request()->session()->regenerate();

        if (function_exists('handle_successful_login')) {
            handle_successful_login();
        }

        return Auth::user();
    }
}
