<?php

namespace Tests\Feature\Identity;

use App\Application\Identity\Actions\Auth\LoginUserWithOtpAction;
use App\Application\Identity\Actions\Auth\LoginUserWithPasswordAction;
use App\Application\Identity\Actions\Auth\QueueLoginAlertsAction;
use App\Application\Identity\Actions\Otp\CreateOtpAction;
use App\Application\Identity\DTOs\LoginAlertData;
use App\Application\Identity\Jobs\SendLoginAlertSmsJob;
use App\Application\Identity\Mails\Auth\LoginAlertMail;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Jobs\SendEmailJob;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\System\Models\Setting;
use App\Infrastructure\Services\Email\EmailService;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Infrastructure\Services\Sms\SmsService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class LoginAlertsTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('channels')]
    public function test_successful_login_queues_only_the_channels_enabled_for_its_role(bool $email, bool $sms): void
    {
        Queue::fake();
        $user = $this->user($email, $sms);
        Auth::login($user, true);

        Queue::assertPushed(SendEmailJob::class, $email ? 1 : 0);
        Queue::assertPushed(SendLoginAlertSmsJob::class, $sms ? 1 : 0);
        if ($email) {
            Queue::assertPushed(SendEmailJob::class, fn ($job) => $job->recipient === $user->email
                && $job->message instanceof LoginAlertMail && $job->afterCommit === true);
        }
    }

    public static function channels(): array
    {
        return [[false, false], [true, false], [false, true], [true, true]];
    }

    public function test_invalid_password_does_not_queue_notifications(): void
    {
        Queue::fake();
        $user = $this->user(true, true);

        $this->assertFalse(Auth::attempt(['email' => $user->email, 'password' => 'wrong']));
        Queue::assertNothingPushed();
    }

    #[DataProvider('loginMethods')]
    public function test_real_website_login_actions_send_one_alert_per_channel(string $method): void
    {
        Queue::fake();
        $user = $this->user(true, true);
        $this->startSession();
        request()->setLaravelSession(app('session.store'));

        if ($method === 'otp') {
            app(CreateOtpAction::class)->execute($user->mobile, '123456', OtpTypeEnum::LOGIN);
            app(LoginUserWithOtpAction::class)->execute($user->mobile, '123456');
        } else {
            app(LoginUserWithPasswordAction::class)->execute($user->mobile, 'password');
        }

        $this->assertAuthenticatedAs($user);
        Queue::assertPushed(SendEmailJob::class, 1);
        Queue::assertPushed(SendLoginAlertSmsJob::class, 1);
    }

    #[DataProvider('loginMethods')]
    public function test_launcher_login_sends_alerts_only_after_token_is_issued(string $method): void
    {
        Queue::fake();
        app(GeneralSettings::class)->forgetCache();
        $user = $this->user(true, true);
        $device = $this->device();

        if ($method === 'otp') {
            app(CreateOtpAction::class)->execute($user->mobile, '123456', OtpTypeEnum::LOGIN);
            $response = $this->postJson(route('api.v1.auth.otp.verify'), ['mobile' => $user->mobile, 'otp_code' => '123456', 'device' => $device]);
        } else {
            $response = $this->postJson(route('api.v1.auth.login'), ['login' => $user->email, 'password' => 'password', 'device' => $device]);
        }

        $response->assertOk()->assertJsonStructure(['data' => ['access_token']]);
        Queue::assertPushed(SendEmailJob::class, 1);
        Queue::assertPushed(SendLoginAlertSmsJob::class, 1);
        Queue::assertPushed(SendEmailJob::class, fn ($job) => $job->message->login->source === 'launcher');
    }

    public static function loginMethods(): array
    {
        return [['password'], ['otp']];
    }

    public function test_failed_launcher_device_proof_does_not_send_alerts(): void
    {
        Queue::fake();
        app(GeneralSettings::class)->forgetCache();
        $user = $this->user(true, true);
        $device = $this->device();
        $device['public_key_fingerprint'] = str_repeat('0', 64);

        $this->postJson(route('api.v1.auth.login'), ['login' => $user->email, 'password' => 'password', 'device' => $device])->assertStatus(422);
        Queue::assertNothingPushed();
    }

    public function test_remember_cookie_restoration_does_not_queue_notifications(): void
    {
        Queue::fake();
        $user = $this->user(true, true);
        Auth::shouldReceive('guard')->with('web')->once()->andReturn(new class
        {
            public function viaRemember(): bool
            {
                return true;
            }
        });

        event(new Login('web', $user, true));
        Queue::assertNothingPushed();
    }

    public function test_missing_email_does_not_prevent_sms_and_inactive_accounts_do_not_send(): void
    {
        Queue::fake();
        $user = $this->user(true, true);
        $user->update(['email' => null]);
        app(QueueLoginAlertsAction::class)->execute($user, Request::create('/login'));

        Queue::assertNotPushed(SendEmailJob::class);
        Queue::assertPushed(SendLoginAlertSmsJob::class, 1);

        $user->update(['status' => UserStatusEnum::INACTIVE]);
        app(QueueLoginAlertsAction::class)->execute($user, Request::create('/login'));
        Queue::assertPushed(SendLoginAlertSmsJob::class, 1);
    }

    public function test_email_dispatch_failure_does_not_fail_login_or_prevent_sms(): void
    {
        Queue::fake();
        $user = $this->user(true, true);
        $this->mock(EmailService::class)->shouldReceive('queue')->once()->andThrow(new RuntimeException('Mail queue unavailable'));

        Auth::login($user);

        $this->assertAuthenticatedAs($user);
        Queue::assertPushed(SendLoginAlertSmsJob::class, 1);
    }

    public function test_request_details_are_captured_before_the_job_runs(): void
    {
        Queue::fake();
        $user = $this->user(true, true);
        $request = Request::create('/api/v1/auth/login', server: ['REMOTE_ADDR' => '203.0.113.9', 'HTTP_USER_AGENT' => 'ModdingWay Launcher/1.0']);
        app(QueueLoginAlertsAction::class)->execute($user, $request, 'launcher');

        Queue::assertPushed(SendEmailJob::class, fn ($job) => $job->message->login->ip === '203.0.113.9'
            && $job->message->login->sourceLabel() === 'لانچر'
            && $job->message->login->userAgent === 'ModdingWay Launcher/1.0');
    }

    public function test_sms_send_failure_does_not_prevent_email_or_successful_authentication(): void
    {
        Queue::fake([SendEmailJob::class]);
        $user = $this->user(true, true);

        // Resolving the SMS service fails independently from the email job.
        $this->app->bind(SmsService::class, fn () => throw new RuntimeException('SMS unavailable'));
        Auth::login($user);

        $this->assertAuthenticatedAs($user);
        Queue::assertPushed(SendEmailJob::class, 1);
    }

    public function test_worker_reads_updated_shared_settings_before_the_next_job(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->set('smsir_login_pattern', '100');
        $this->assertSame('100', $settings->get('smsir_login_pattern'));

        // Simulate a settings edit and cache invalidation performed by another process.
        Setting::query()->where('group', 'general')->where('key', 'smsir_login_pattern')->update(['value' => json_encode('200')]);
        Cache::forget(CacheKey::settingsGroup('general'));
        $this->assertSame('100', $settings->get('smsir_login_pattern'));

        event(new JobProcessing('sync', $this->createMock(Job::class)));
        $this->assertSame('200', $settings->get('smsir_login_pattern'));
    }

    public function test_sms_pattern_settings_render_save_and_reject_duplicate_parameters(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $this->actingAs($admin)->get(route('admin.settings.general', ['tab' => 'sms-panel']))
            ->assertOk()->assertSee('smsir_login_pattern')->assertSee('melipayamak_login_pattern');

        $url = route('admin.settings.general.store');
        $this->put($url, ['smsir_login_pattern' => '1234', 'smsir_login_name_parameter' => 'Account'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('1234', app(GeneralSettings::class)->get('smsir_login_pattern'));

        $this->put($url, [
            'smsir_login_pattern' => '5678', 'smsir_login_name_parameter' => 'Same', 'smsir_login_time_parameter' => 'Same',
        ])->assertSessionHasErrors('smsir_login_time_parameter');
        $this->assertSame('1234', app(GeneralSettings::class)->get('smsir_login_pattern'));
    }

    public function test_email_job_sends_rtl_html_and_plain_text_without_queueing_again(): void
    {
        config(['mail.default' => 'array']);
        $data = new LoginAlertData('<script>alert(1)</script>', CarbonImmutable::now(), '203.0.113.9', '<b>Browser</b>', 'web');
        $job = unserialize(serialize(new SendEmailJob('recipient@example.com', new LoginAlertMail($data))));
        $job->handle(app(EmailService::class));

        $sent = Mail::getSymfonyTransport()->messages()->sole()->getOriginalMessage();
        $this->assertSame('recipient@example.com', $sent->getTo()[0]->getAddress());
        $this->assertStringContainsString('dir="rtl"', $sent->getHtmlBody());
        $this->assertStringContainsString('203.0.113.9', $sent->getTextBody());
        $this->assertStringNotContainsString('<script>', $sent->getHtmlBody());
        $this->assertStringContainsString('&lt;b&gt;Browser&lt;/b&gt;', $sent->getHtmlBody());
    }

    public function test_queued_email_is_sent_only_after_commit_and_discarded_on_rollback(): void
    {
        Mail::fake();
        $data = new LoginAlertData('Test', CarbonImmutable::now(), '203.0.113.9', '', 'web');
        $email = app(EmailService::class);

        DB::beginTransaction();
        $email->queue('recipient@example.com', new LoginAlertMail($data));
        Mail::assertNothingSent();
        DB::rollBack();
        Mail::assertNothingSent();

        DB::beginTransaction();
        $email->queue('recipient@example.com', new LoginAlertMail($data));
        Mail::assertNothingSent();
        DB::commit();
        Mail::assertSent(LoginAlertMail::class, 1);
    }

    #[DataProvider('protectedRoles')]
    public function test_admin_can_configure_alerts_for_protected_roles_without_changing_permissions(string $flag): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $target = Role::factory()->create([$flag => true]);
        $this->actingAs($admin)->get(route('admin.roles.login-alerts.edit', $target))
            ->assertOk()->assertSee('اعلان ورود');

        $this->put(route('admin.roles.login-alerts.update', $target), [
            'login_email_enabled' => '1', 'login_sms_enabled' => '1',
            'title' => 'Tampered', 'is_super_admin' => false,
        ])->assertRedirect(route('admin.roles.login-alerts.edit', $target));

        $fresh = $target->fresh();
        $this->assertTrue($fresh->login_email_enabled);
        $this->assertTrue($fresh->login_sms_enabled);
        $this->assertSame($target->title, $fresh->title);
        $this->assertTrue($fresh->{$flag});

        $this->put(route('admin.roles.login-alerts.update', $target), [
            'login_email_enabled' => '0', 'login_sms_enabled' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertFalse($target->fresh()->login_sms_enabled);
    }

    public static function protectedRoles(): array
    {
        return [['is_super_admin'], ['is_default']];
    }

    public function test_role_preferences_require_permission_and_validate_both_switches(): void
    {
        $user = $this->user(false, false);
        Gate::define('admin.display', fn () => true);
        Gate::define('role.edit', fn () => false);
        $url = route('admin.roles.login-alerts.update', $user->role);
        $this->actingAs($user)->put($url, ['login_email_enabled' => 1, 'login_sms_enabled' => 1])->assertForbidden();

        Gate::define('role.edit', fn () => true);
        $this->put($url, ['login_email_enabled' => 'yes'])->assertSessionHasErrors(['login_email_enabled', 'login_sms_enabled']);
        $this->assertFalse($user->role->fresh()->login_email_enabled);
    }

    private function user(bool $email, bool $sms): User
    {
        return User::factory()->create([
            'role_id' => Role::factory()->create(['login_email_enabled' => $email, 'login_sms_enabled' => $sms])->id,
            'mobile' => '09123456789',
        ]);
    }

    private function device(): array
    {
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        $publicPem = openssl_pkey_get_details($key)['key'];
        $publicDer = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $publicPem));
        $device = [
            'id' => (string) Str::uuid(), 'name' => 'Test PC',
            'fingerprint_hash' => hash('sha256', 'test-device'),
            'public_key' => base64_encode($publicDer), 'public_key_fingerprint' => hash('sha256', $publicDer),
            'proof_timestamp' => now()->timestamp, 'proof_nonce' => base64_encode(random_bytes(24)),
            'platform' => 'windows', 'app_version' => '1.0.0',
        ];
        $canonical = implode("\n", ['MWLauncher.DeviceProof.v1', $device['id'], $device['fingerprint_hash'], $device['public_key_fingerprint'], $device['proof_timestamp'], $device['proof_nonce']]);
        openssl_sign($canonical, $signature, $key, OPENSSL_ALGO_SHA256);
        $device['proof_signature'] = base64_encode($signature);

        return $device;
    }
}
