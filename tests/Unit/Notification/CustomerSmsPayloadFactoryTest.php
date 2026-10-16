<?php

namespace Tests\Unit\Notification;

use App\Application\Notification\Support\CustomerSmsPayloadFactory;
use App\Domain\Notification\Enums\CustomerSmsType;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Infrastructure\Services\Sms\Providers\FarazSmsProvider;
use App\Infrastructure\Services\Sms\Providers\MeliPayamakProvider;
use App\Infrastructure\Services\Sms\Providers\SmsIrProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class CustomerSmsPayloadFactoryTest extends TestCase
{
    #[DataProvider('providersAndTypes')]
    public function test_provider_specific_names_and_positional_order(string $provider, string $prefix, CustomerSmsType $type): void
    {
        $settings = $this->mock(GeneralSettings::class);
        $settings->shouldReceive('get')->with('default_sms_provider')->andReturn($provider);
        $settings->shouldReceive('get')->with($prefix.'_'.$type->value.'_pattern')->andReturn('1234');
        $values = [];
        $mapped = [];
        foreach ($type->parameters() as $key => $defaultName) {
            $values[$key] = $key === 'name' ? 'مشتری' : '1042';
            $mapped['custom_'.$key] = $values[$key];
            if ($prefix !== 'melipayamak') {
                $settings->shouldReceive('get')->with($prefix.'_'.$type->value.'_'.$key.'_parameter', $defaultName)->andReturn('custom_'.$key);
            }
        }

        // Input array order must not change the parameter order required by MeliPayamak.
        $payload = app(CustomerSmsPayloadFactory::class)->make($type, array_reverse($values, true));
        $this->assertSame('1234', $payload['pattern_id']);
        $this->assertSame($prefix === 'melipayamak' ? array_values($values) : $mapped, $payload['values']);
    }

    public static function providersAndTypes(): array
    {
        $cases = [];
        foreach ([SmsIrProvider::class => 'smsir', FarazSmsProvider::class => 'farazsms', MeliPayamakProvider::class => 'melipayamak'] as $provider => $prefix) {
            foreach (CustomerSmsType::cases() as $type) {
                $cases[] = [$provider, $prefix, $type];
            }
        }

        return $cases;
    }

    public function test_missing_pattern_does_not_fall_back_to_otp_or_login_patterns(): void
    {
        $settings = $this->mock(GeneralSettings::class);
        $settings->shouldReceive('get')->with('default_sms_provider')->andReturn(SmsIrProvider::class);
        $settings->shouldReceive('get')->with('smsir_order_paid_pattern')->andReturn(null);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Customer SMS pattern [smsir_order_paid] is not configured.');

        app(CustomerSmsPayloadFactory::class)->make(CustomerSmsType::ORDER_PAID, ['name' => 'Test', 'order_number' => '1042']);
    }

    public function test_duplicate_parameter_names_are_rejected_before_sending(): void
    {
        $settings = $this->mock(GeneralSettings::class);
        $settings->shouldReceive('get')->with('default_sms_provider')->andReturn(FarazSmsProvider::class);
        $settings->shouldReceive('get')->with('farazsms_ticket_reply_pattern')->andReturn('pattern');
        $settings->shouldReceive('get')->with('farazsms_ticket_reply_name_parameter', 'Name')->andReturn('Same');
        $settings->shouldReceive('get')->with('farazsms_ticket_reply_ticket_number_parameter', 'TicketNumber')->andReturn('Same');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Customer SMS parameter names must be non-empty and unique.');

        app(CustomerSmsPayloadFactory::class)->make(CustomerSmsType::TICKET_REPLY, ['name' => 'Test', 'ticket_number' => '1042']);
    }
}
