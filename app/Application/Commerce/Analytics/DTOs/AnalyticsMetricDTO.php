<?php

namespace App\Application\Commerce\Analytics\DTOs;

final readonly class AnalyticsMetricDTO
{
    public function __construct(
        public int|float      $value,
        public int|float|null $comparisonValue = null,
        public ?float         $changePercentage = null,
    ) {
    }

    public static function compare(
        int|float $current,
        int|float|null $previous,
    ): self {
        if ($previous === null) {
            return new self($current);
        }

        $changePercentage = match (true) {
            (float)$previous === 0.0 && (float)$current === 0.0 => 0.0,
            (float)$previous === 0.0 => null,
            default => (($current - $previous) / abs($previous)) * 100,
        };

        return new self(
            value: $current,
            comparisonValue: $previous,
            changePercentage: $changePercentage,
        );
    }

    public function direction(): string
    {
        if ($this->comparisonValue === null) {
            return 'none';
        }

        return match (true) {
            $this->value > $this->comparisonValue => 'up',
            $this->value < $this->comparisonValue => 'down',
            default => 'flat',
        };
    }
}
