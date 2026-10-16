<?php

namespace Tests\Unit\Identity;

use App\Application\Identity\DTOs\LoginAlertData;
use App\Application\Identity\Jobs\SendLoginAlertSmsJob;
use App\Application\Identity\Support\LoginAlertSmsPayloadFactory;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Infrastructure\Services\Sms\Providers\FarazSmsProvider;
use App\Infrastructure\Services\Sms\Providers\MeliPayamakProvider;
use App\Infrastructure\Services\Sms\Providers\SmsIrProvider;
use App\Infrastructure\Services\Sms\SmsService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class LoginAlertSmsPayloadFactoryTest extends TestCase
{
    #[DataProvider('providers')]
    public function test_job_uses_the_existing_sms_service_with_provider_specific_parameters(string $provider, string $prefix): void
    {
        $settings = $this->mock(GeneralSettings::class);
        $settings->shouldReceive('get')->with('default_sms_provider')->andReturn($provider);
        $settings->shouldReceive('get')->with($prefix.'_login_pattern')->andReturn('1234');
        foreach (['name', 'time', 'ip', 'source'] as $key) {
            $settings->shouldReceive('get')->with($prefix.'_login_'.$key.'_parameter', ucfirst($key))->andReturn('custom_'.$key);
        }
        $login = new LoginAlertData('یوسف', CarbonImmutable::parse('2026-10-16 09:00:00', 'UTC'), '203.0.113.9', '', 'launcher');
        $values = ['یوسف', $login->time(), '203.0.113.9', 'لانچر'];
        $expected = $prefix === 'melipayamak' ? $values : array_combine(['custom_name', 'custom_time', 'custom_ip', 'custom_source'], $values);
        $sms = $this->mock(SmsService::class);
        $sms->shouldReceive('sendPattern')->once()->with('09123456789', '1234', $expected)->andReturn('message-id');

        (new SendLoginAlertSmsJob('09123456789', $login))->handle($sms, app(LoginAlertSmsPayloadFactory::class));
        $this->assertStringEndsWith('12:30:00', $login->time());
    }

    public static function providers(): array
    {
        return [[SmsIrProvider::class, 'smsir'], [FarazSmsProvider::class, 'farazsms'], [MeliPayamakProvider::class, 'melipayamak']];
    }

    public function test_missing_pattern_fails_visibly_instead_of_reusing_the_otp_template(): void
    {
        $settings = $this->mock(GeneralSettings::class);
        $settings->shouldReceive('get')->with('default_sms_provider')->andReturn(SmsIrProvider::class);
        $settings->shouldReceive('get')->with('smsir_login_pattern')->andReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Login alert SMS pattern is not configured');
        app(LoginAlertSmsPayloadFactory::class)->make(new LoginAlertData('Test', CarbonImmutable::now(), '203.0.113.9', '', 'web'));
    }
}
