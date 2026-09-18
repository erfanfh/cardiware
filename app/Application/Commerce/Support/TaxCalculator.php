<?php

namespace App\Application\Commerce\Support;

use App\Infrastructure\Services\Setting\Groups\GeneralSettings;

class TaxCalculator
{
    public function __construct(
        private readonly GeneralSettings $settings,
    ) {
    }

    public function currentRateBasisPoints(): int
    {
        if (!(bool) $this->settings->get('tax_enabled', false)) {
            return 0;
        }

        return $this->rateToBasisPoints(
            $this->settings->get('tax_rate_percent', 0),
        );
    }

    public function calculate(int $taxableAmount): TaxCalculation
    {
        return $this->calculateAtRate(
            $taxableAmount,
            $this->currentRateBasisPoints(),
        );
    }

    public function calculateAtRate(int $taxableAmount, int $rateBasisPoints): TaxCalculation
    {
        $taxableAmount = max(0, $taxableAmount);
        $rateBasisPoints = max(0, min(10_000, $rateBasisPoints));

        // Integer half-up rounding avoids floating point errors for money.
        $taxAmount = intdiv($taxableAmount, 10_000) * $rateBasisPoints;
        $taxAmount += intdiv(
            (($taxableAmount % 10_000) * $rateBasisPoints) + 5_000,
            10_000,
        );

        return new TaxCalculation(
            taxableAmount: $taxableAmount,
            rateBasisPoints: $rateBasisPoints,
            taxAmount: $taxAmount,
            totalAmount: $taxableAmount + $taxAmount,
        );
    }

    public function rateToBasisPoints(mixed $rate): int
    {
        $normalized = str_replace(',', '.', trim((string) $rate));

        if (!preg_match('/^(\d{1,3})(?:\.(\d{1,2}))?$/', $normalized, $matches)) {
            return 0;
        }

        $basisPoints = ((int) $matches[1] * 100)
            + (int) str_pad($matches[2] ?? '', 2, '0');

        return max(0, min(10_000, $basisPoints));
    }
}
