<?php

namespace App\Application\Identity\Listeners;

use App\Application\Identity\Actions\Auth\QueueLoginAlertsAction;
use App\Domain\Identity\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;

final class QueueLoginAlerts
{
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $guard = Auth::guard($event->guard);
        if (method_exists($guard, 'viaRemember') && $guard->viaRemember()) {
            return;
        }

        app(QueueLoginAlertsAction::class)->execute($event->user, request());
    }
}
