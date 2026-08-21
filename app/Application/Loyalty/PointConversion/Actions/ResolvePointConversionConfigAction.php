<?php

namespace App\Application\Loyalty\PointConversion\Actions;

use App\Application\Loyalty\PointConversion\DTOs\PointConversionConfig;

class ResolvePointConversionConfigAction
{
    public function execute(): PointConversionConfig
    {
        return new PointConversionConfig(
            enabled: (bool) get_setting('loyalty', 'point_conversion_enabled', false),
            pointsPerUnit: (int) get_setting('loyalty', 'point_conversion_points_per_unit', 100),
            walletAmountPerUnit: (int) get_setting('loyalty', 'point_conversion_wallet_amount_per_unit', 10_000),
            minimumPoints: (int) get_setting('loyalty', 'point_conversion_minimum_points', 500),
            maximumPointsPerRequest: (int) get_setting('loyalty', 'point_conversion_maximum_points_per_request', 10_000),
            dailyPointsLimit: (int) get_setting('loyalty', 'point_conversion_daily_points_limit', 20_000),
        );
    }
}
