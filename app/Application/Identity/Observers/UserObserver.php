<?php

namespace App\Application\Identity\Observers;

use App\Domain\Identity\Models\User;
use DomainException;

class UserObserver
{
    public function deleting(User $user): void
    {
        if ($user->isForceDeleting()) {
            return;
        }

        if ($user->trashed()) {
            return;
        }

        $user->forceFill([
            'deleted_mobile' => $user->mobile,
            'deleted_email' => $user->email,
            'mobile' => null,
            'email' => null,
        ])->saveQuietly();
    }

    public function restoring(User $user): void
    {
        $mobile = $user->deleted_mobile;
        $email = $user->deleted_email;

        if (
            filled($mobile) &&
            User::query()
                ->whereKeyNot($user->getKey())
                ->where('mobile', $mobile)
                ->exists()
        ) {
            throw new DomainException(
                'امکان بازیابی کاربر وجود ندارد؛ شماره موبایل او توسط کاربر دیگری استفاده شده است.'
            );
        }

        if (
            filled($email) &&
            User::query()
                ->whereKeyNot($user->getKey())
                ->where('email', $email)
                ->exists()
        ) {
            throw new DomainException(
                'امکان بازیابی کاربر وجود ندارد؛ ایمیل او توسط کاربر دیگری استفاده شده است.'
            );
        }

        /*
         * متد restore بعد از اجرای رویداد restoring،
         * همین تغییرات را همراه deleted_at ذخیره می‌کند.
         */
        $user->forceFill([
            'mobile' => $mobile,
            'email' => $email,
            'deleted_mobile' => null,
            'deleted_email' => null,
        ]);
    }
}
