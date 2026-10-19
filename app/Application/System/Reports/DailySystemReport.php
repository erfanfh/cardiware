<?php

namespace App\Application\System\Reports;

use App\Domain\System\Models\BackupRun;

final class DailySystemReport
{
    public function __construct(private readonly DependencySecurityAudit $audit) {}

    public function collect(): array
    {
        $until = now()->toImmutable();
        $since = $until->subHours(24);
        $runs = BackupRun::query()
            ->where(function ($query) use ($since, $until): void {
                $query->whereBetween('finished_at', [$since, $until])
                    ->orWhere(function ($query) use ($until): void {
                        $query->whereNull('finished_at')
                            ->whereIn('status', ['queued', 'running'])
                            ->where('created_at', '<=', $until);
                    });
            })
            ->get(['status']);

        return [
            'since' => $since->timezone('Asia/Tehran')->format('Y-m-d H:i'),
            'until' => $until->timezone('Asia/Tehran')->format('Y-m-d H:i'),
            'composer' => match ($this->audit->composerNeedsAttention()) {
                true => 'موارد امنیتی پکیج‌ها باید بررسی و رفع شوند.',
                false => 'مورد امنیتی نیازمند رسیدگی گزارش نشد.',
                null => 'نامشخص؛ اجرای composer audit ناموفق بود و نیاز به بررسی دارد.',
            },
            'npm' => match ($this->audit->npmHasCriticalVulnerabilities()) {
                true => 'آسیب‌پذیری Critical وجود دارد و باید بررسی و رفع شود.',
                false => 'آسیب‌پذیری Critical گزارش نشد.',
                null => 'نامشخص؛ اجرای npm audit ناموفق بود و نیاز به بررسی دارد.',
            },
            'backups' => [
                'succeeded' => $runs->where('status', 'succeeded')->count(),
                'failed' => $runs->where('status', 'failed')->count(),
                'running' => $runs->where('status', 'running')->count(),
                'queued' => $runs->where('status', 'queued')->count(),
            ],
        ];
    }
}
