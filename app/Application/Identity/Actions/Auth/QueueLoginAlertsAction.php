<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\DTOs\LoginAlertData;
use App\Application\Identity\Jobs\SendLoginAlertSmsJob;
use App\Application\Identity\Mails\Auth\LoginAlertMail;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Email\EmailService;
use Illuminate\Http\Request;
use Throwable;

final class QueueLoginAlertsAction
{
    public function execute(User $user, Request $request, string $source = 'web'): void
    {
        // Notification infrastructure must never turn successful authentication into an error.
        try {
            if ($user->status !== UserStatusEnum::ACTIVE) {
                return;
            }

            $role = $user->role()->first();
            if (! $role || (! $role->login_email_enabled && ! $role->login_sms_enabled)) {
                return;
            }

            $data = LoginAlertData::fromRequest(
                trim((string) ($user->display_name ?: $user->full_name ?: $user->username)) ?: 'کاربر عزیز',
                $request,
                $source,
            );
        } catch (Throwable $exception) {
            report($exception);

            return;
        }

        if ($role->login_email_enabled && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                app(EmailService::class)->queue($user->email, new LoginAlertMail($data));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        if ($role->login_sms_enabled && filled($user->mobile)) {
            try {
                SendLoginAlertSmsJob::dispatch($user->mobile, $data)->afterCommit();
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
