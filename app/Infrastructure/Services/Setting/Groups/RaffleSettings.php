<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class RaffleSettings extends BaseSettings
{
    protected string $group = 'raffle';

    protected array $allowedKeys = [
        'winner_sms_enabled',
        'winner_reminder_sms_enabled',
        'winner_reminder_hours_before_deadline',
        'auto_start_draw_enabled',
        'show_in_header_navigation',

        'smsir_winner_template_id',
        'smsir_winner_name_parameter',
        'smsir_winner_campaign_parameter',
        'smsir_winner_prize_parameter',
        'smsir_winner_deadline_parameter',
        'smsir_reminder_template_id',
        'smsir_reminder_name_parameter',
        'smsir_reminder_campaign_parameter',
        'smsir_reminder_prize_parameter',
        'smsir_reminder_deadline_parameter',

        'farazsms_winner_pattern',
        'farazsms_winner_name_attribute',
        'farazsms_winner_campaign_attribute',
        'farazsms_winner_prize_attribute',
        'farazsms_winner_deadline_attribute',
        'farazsms_reminder_pattern',
        'farazsms_reminder_name_attribute',
        'farazsms_reminder_campaign_attribute',
        'farazsms_reminder_prize_attribute',
        'farazsms_reminder_deadline_attribute',

        'melipayamak_winner_pattern',
        'melipayamak_reminder_pattern',
    ];

    protected array $casts = [
        'winner_sms_enabled' => BooleanCast::class,
        'winner_reminder_sms_enabled' => BooleanCast::class,
        'winner_reminder_hours_before_deadline' => IntegerCast::class,
        'auto_start_draw_enabled' => BooleanCast::class,
        'show_in_header_navigation' => BooleanCast::class,
    ];
}
