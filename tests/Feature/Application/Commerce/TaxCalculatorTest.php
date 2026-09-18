<?php

namespace Tests\Feature\Application\Commerce;

use App\Application\Commerce\Support\TaxCalculator;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\WalletTopUp;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use Tests\TestCase;

class TaxCalculatorTest extends TestCase
{
    public function test_disabled_tax_preserves_the_existing_total_exactly(): void
    {
        $settings = $this->settings(enabled: false, rate: '9.75');

        $result = (new TaxCalculator($settings))->calculate(123_456);

        $this->assertSame(0, $result->rateBasisPoints);
        $this->assertSame(0, $result->taxAmount);
        $this->assertSame(123_456, $result->totalAmount);
    }

    public function test_enabled_tax_supports_two_decimal_places_and_integer_money_rounding(): void
    {
        $settings = $this->settings(enabled: true, rate: '9.75');

        $result = (new TaxCalculator($settings))->calculate(123_456);

        $this->assertSame(975, $result->rateBasisPoints);
        $this->assertSame(12_037, $result->taxAmount);
        $this->assertSame(135_493, $result->totalAmount);
    }

    public function test_tax_is_calculated_from_the_net_amount_passed_after_discounts(): void
    {
        $settings = $this->settings(enabled: true, rate: '10');

        $result = (new TaxCalculator($settings))->calculate(90_000);

        $this->assertSame(90_000, $result->taxableAmount);
        $this->assertSame(9_000, $result->taxAmount);
        $this->assertSame(99_000, $result->totalAmount);
    }

    public function test_order_exposes_its_tax_but_wallet_top_up_is_not_taxed_again(): void
    {
        $order = new Order([
            'tax_amount' => 9_000,
            'total_amount' => 99_000,
        ]);

        $topUp = new WalletTopUp([
            'amount' => 50_000,
        ]);

        $this->assertSame(9_000, $order->getPayableTaxAmount());
        $this->assertSame(0, $topUp->getPayableTaxAmount());
    }

    private function settings(bool $enabled, string $rate): GeneralSettings
    {
        $settings = $this->createMock(GeneralSettings::class);
        $settings->method('get')->willReturnCallback(
            static fn (string $key, mixed $default = null): mixed => match ($key) {
                'tax_enabled' => $enabled,
                'tax_rate_percent' => $rate,
                default => $default,
            },
        );

        return $settings;
    }
}
