<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class TicketSettings extends BaseSettings
{
    protected string $group = 'ticket';

    protected array $allowedKeys = [
        'ticket_submission_tips',
    ];

    protected array $casts = [
        'ticket_submission_tips' => RepeaterArrayCast::class,
    ];
}
