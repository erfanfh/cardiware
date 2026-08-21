<?php

namespace App\Application\Loyalty\PointConversion\DTOs;

final readonly class PointConversionConfig
{
    public function __construct(
        public bool $enabled,
        public int $pointsPerUnit,
        public int $walletAmountPerUnit,
        public int $minimumPoints,
        public int $maximumPointsPerRequest,
        public int $dailyPointsLimit,
    ) {}

    public function isAvailable(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if (
            $this->pointsPerUnit <= 0
            || $this->walletAmountPerUnit <= 0
            || $this->minimumPoints < $this->pointsPerUnit
            || $this->minimumPoints % $this->pointsPerUnit !== 0
        ) {
            return false;
        }

        if (
            $this->maximumPointsPerRequest > 0
            && (
                $this->maximumPointsPerRequest < $this->minimumPoints
                || $this->maximumPointsPerRequest % $this->pointsPerUnit !== 0
            )
        ) {
            return false;
        }

        return $this->dailyPointsLimit === 0
            || (
                $this->dailyPointsLimit >= $this->minimumPoints
                && $this->dailyPointsLimit % $this->pointsPerUnit === 0
            );
    }

    public function walletAmountFor(int $points): int
    {
        return intdiv($points, $this->pointsPerUnit) * $this->walletAmountPerUnit;
    }

    public function floorToConversionUnit(int $points): int
    {
        if ($points <= 0 || $this->pointsPerUnit <= 0) {
            return 0;
        }

        return intdiv($points, $this->pointsPerUnit) * $this->pointsPerUnit;
    }
}
