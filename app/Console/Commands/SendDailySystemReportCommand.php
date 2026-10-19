<?php

namespace App\Console\Commands;

use App\Application\System\Mails\DailySystemReportMail;
use App\Application\System\Reports\DailySystemReport;
use App\Infrastructure\Services\Email\EmailService;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use Illuminate\Console\Command;

final class SendDailySystemReportCommand extends Command
{
    protected $signature = 'system:send-daily-report';

    protected $description = 'Email the daily dependency security and last 24 hours backup summary.';

    public function handle(GeneralSettings $settings, DailySystemReport $report, EmailService $email): int
    {
        BaseSettings::clearResolvedGroups();
        $recipients = preg_split('/[\s,;]+/', trim((string) $settings->get('system_report_recipients', '')), -1, PREG_SPLIT_NO_EMPTY);
        $recipients = array_unique(array_map('strtolower', $recipients));

        if ($recipients === []) {
            $this->info('Daily system report skipped: no recipients configured.');

            return self::SUCCESS;
        }

        // Validate before running audits or queueing any partial delivery.
        foreach ($recipients as $recipient) {
            if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                $this->error('Daily system report has an invalid recipient.');

                return self::FAILURE;
            }
        }

        $message = new DailySystemReportMail($report->collect());
        foreach ($recipients as $recipient) {
            $email->queue($recipient, $message);
        }

        $this->info('Daily system report queued for '.count($recipients).' recipient(s).');

        return self::SUCCESS;
    }
}
