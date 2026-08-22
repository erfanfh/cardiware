<?php

namespace App\Domain\Raffle\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class DrawCampaignEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'campaign_id', 'actor_user_id', 'type', 'subject_type', 'subject_id', 'payload', 'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Raffle audit events are immutable.');
        });
        static::deleting(function (): never {
            throw new LogicException('Raffle audit events cannot be deleted.');
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DrawCampaign::class, 'campaign_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function label(): string
    {
        return match ($this->type) {
            'campaign_created' => 'ایجاد کمپین',
            'campaign_updated' => 'ویرایش تنظیمات کمپین',
            'campaign_status_changed' => 'تغییر وضعیت کمپین',
            'campaign_cancelled' => 'لغو کمپین',
            'tickets_issued' => 'صدور بلیط',
            'tickets_invalidated' => 'ابطال بلیط',
            'draw_started' => 'شروع قرعه‌کشی',
            'draw_redraw_started' => 'شروع قرعه‌کشی مجدد',
            'draw_completed' => 'تکمیل قرعه‌کشی',
            'draw_failed' => 'خطای اجرای قرعه‌کشی',
            'winners_selected' => 'انتخاب برندگان',
            'winner_confirmed' => 'تأیید دریافت جایزه',
            'winner_rejected' => 'رد یا انصراف برنده',
            'winner_expired' => 'انقضای مهلت برنده',
            'reserve_promoted' => 'ارتقای نفر ذخیره',
            'prize_delivered' => 'تحویل جایزه',
            'winner_sms_sent' => 'ارسال پیامک برنده',
            'winner_sms_failed' => 'خطای پیامک برنده',
            'winner_reminder_sms_sent' => 'ارسال پیامک یادآوری',
            'winner_reminder_sms_failed' => 'خطای پیامک یادآوری',
            default => str_replace('_', ' ', $this->type),
        };
    }
}
