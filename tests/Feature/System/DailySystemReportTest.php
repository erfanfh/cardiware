<?php

namespace Tests\Feature\System;

use App\Application\Shared\Jobs\SendEmailJob;
use App\Application\System\Mails\DailySystemReportMail;
use App\Application\System\Reports\DailySystemReport;
use App\Domain\Identity\Models\User;
use App\Domain\System\Models\BackupRun;
use App\Infrastructure\Services\Email\EmailService;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class DailySystemReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Queue::fake();
        Process::preventStrayProcesses();
        $this->travelTo(now()->setTimezone('Asia/Tehran')->setDate(2026, 10, 19)->setTime(20, 0));
    }

    public function test_no_recipients_skips_audits_and_email(): void
    {
        $this->artisan('system:send-daily-report')->assertSuccessful();

        Process::assertNothingRan();
        Queue::assertNothingPushed();
    }

    public function test_configured_recipients_get_one_private_copy_through_the_central_email_service(): void
    {
        app(GeneralSettings::class)->set('system_report_recipients', "Admin@example.com\nops@example.com, admin@example.com");
        $this->fakeAudits();

        $this->artisan('system:send-daily-report')->assertSuccessful();

        Queue::assertPushed(SendEmailJob::class, 2);
        Queue::assertPushed(SendEmailJob::class, fn ($job) => $job->recipient === 'ops@example.com'
            && $job->message instanceof DailySystemReportMail && $job->afterCommit === true);
        $job = Queue::pushed(SendEmailJob::class)->first();
        $job->handle(app(EmailService::class));
        $message = Mail::mailer()->getSymfonyTransport()->messages()->first()->getOriginalMessage();
        $this->assertCount(1, $message->getTo());
        $this->assertSame('admin@example.com', $message->getTo()[0]->getAddress());
        $this->assertSame([], $message->getCc());
        $this->assertSame([], $message->getBcc());
        $this->assertStringContainsString('گزارش روزانه وضعیت سیستم', $message->getHtmlBody());
        $this->assertStringContainsString('هیچ بک‌آپ تکمیل‌شده‌ای', $message->getTextBody());
        Process::assertRanTimes(fn () => true, 2);
    }

    public function test_backup_window_counts_completion_time_and_retains_unfinished_runs(): void
    {
        $this->fakeAudits();
        foreach ([
            ['succeeded', now()->subHours(24)],
            ['succeeded', now()->subMinute()],
            ['failed', now()->subHours(2)],
            ['succeeded', now()->subHours(24)->subSecond()],
            ['failed', now()->addSecond()],
            ['queued', null],
            ['running', null],
        ] as [$status, $finishedAt]) {
            $run = new BackupRun([
                'type' => 'full', 'trigger' => 'schedule', 'status' => $status,
                'started_at' => now()->subHours(30), 'finished_at' => $finishedAt,
            ]);
            $run->created_at = now()->subHours(30);
            $run->save();
        }

        $report = app(DailySystemReport::class)->collect();

        $this->assertSame(['succeeded' => 2, 'failed' => 1, 'running' => 1, 'queued' => 1], $report['backups']);
        $this->assertSame('2026-10-18 20:00', $report['since']);
        $this->assertSame('2026-10-19 20:00', $report['until']);
    }

    public function test_audit_failure_still_sends_backup_results_with_unknown_security_status(): void
    {
        app(GeneralSettings::class)->set('system_report_recipients', 'admin@example.com');
        Process::fake(['*' => Process::result(output: '{"error":"unavailable"}', exitCode: 1)]);

        $this->artisan('system:send-daily-report')->assertSuccessful();

        Queue::assertPushed(SendEmailJob::class, function ($job) {
            $this->assertStringContainsString('نامشخص', $job->message->report['composer']);
            $this->assertStringContainsString('نامشخص', $job->message->report['npm']);

            return true;
        });
    }

    public function test_report_contains_only_security_summaries(): void
    {
        Process::fake(fn ($process) => Process::result(output: json_encode(
            $process->command[0] === config('system-report.composer_binary')
                ? ['advisories' => ['secret/package' => [['title' => 'Secret advisory details']]]]
                : ['metadata' => ['vulnerabilities' => ['critical' => 1]]]
        ), exitCode: 1));

        $mail = new DailySystemReportMail(app(DailySystemReport::class)->collect());
        $html = $mail->render();
        $this->assertStringContainsString('باید بررسی و رفع شوند', $html);
        $this->assertStringContainsString('آسیب‌پذیری Critical وجود دارد', $html);
        $this->assertStringNotContainsString('secret/package', $html);
        $this->assertStringNotContainsString('Secret advisory details', $html);
    }

    public function test_settings_validate_addresses_save_and_clear_recipients_and_require_permission(): void
    {
        $user = User::factory()->create();
        Gate::define('admin.display', fn () => true);
        Gate::define('setting.general', fn () => false);
        $this->actingAs($user)->put(route('admin.settings.general.store'), [
            'system_report_recipients' => 'admin@example.com',
        ])->assertForbidden();

        Gate::define('setting.general', fn () => true);
        $this->put(route('admin.settings.general.store'), [
            'system_report_recipients' => "admin@example.com\ninvalid-email",
        ])->assertSessionHasErrors('system_report_recipients');
        $this->put(route('admin.settings.general.store'), [
            'system_report_recipients' => implode("\n", array_fill(0, 21, 'admin@example.com')),
        ])->assertSessionHasErrors('system_report_recipients');
        $this->put(route('admin.settings.general.store'), [
            'system_report_recipients' => "admin@example.com\nops@example.com", 'active_tab' => 'system-report',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.settings.general', ['tab' => 'system-report']));
        $this->assertSame("admin@example.com\nops@example.com", app(GeneralSettings::class)->get('system_report_recipients'));
        $this->get(route('admin.settings.general', ['tab' => 'system-report']))->assertOk()->assertSee('system_report_recipients');
        $this->put(route('admin.settings.general.store'), ['system_report_recipients' => ''])->assertSessionHasNoErrors();
        $this->assertEmpty(app(GeneralSettings::class)->get('system_report_recipients'));
    }

    public function test_invalid_persisted_address_does_not_queue_partial_delivery(): void
    {
        app(GeneralSettings::class)->set('system_report_recipients', 'admin@example.com,invalid');
        $this->artisan('system:send-daily-report')->assertFailed();
        Process::assertNothingRan();
        Queue::assertNothingPushed();
    }

    public function test_schedule_runs_at_20_tehran_once_per_server_without_overlap(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command ?? '', 'system:send-daily-report'));
        $this->assertCount(1, $events);
        $event = $events->first();
        $this->assertSame('0 20 * * *', $event->expression);
        $this->assertSame('Asia/Tehran', $event->timezone);
        $this->assertTrue($event->onOneServer);
        $this->assertTrue($event->withoutOverlapping);
        $this->assertTrue($event->runInBackground);
    }

    private function fakeAudits(): void
    {
        Process::fake(fn ($process) => Process::result(output: json_encode(
            $process->command[0] === config('system-report.composer_binary')
                ? ['advisories' => []]
                : ['metadata' => ['vulnerabilities' => ['high' => 3, 'critical' => 0]]]
        )));
    }
}
