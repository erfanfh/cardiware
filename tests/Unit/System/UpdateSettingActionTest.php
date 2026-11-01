<?php

namespace Tests\Unit\System;

use App\Application\Shared\Cache\Invalidators\SettingCacheInvalidator;
use App\Application\System\Actions\UpdateSettingAction;
use App\Infrastructure\Services\Setting\Groups\ProductLandingsSettings;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class UpdateSettingActionTest extends TestCase
{
    public function test_throwing_variant_preserves_the_original_storage_failure(): void
    {
        $failure = new RuntimeException('Underlying storage failure');
        $settings = $this->mock(ProductLandingsSettings::class, function ($mock) use ($failure): void {
            $mock->shouldReceive('getAllowedKeys')->once()->andReturn(['ultimate']);
            $mock->shouldReceive('set')->with('ultimate', ['brand' => 'PGL'])->once()->andThrow($failure);
        });
        $this->app->instance('\\'.ProductLandingsSettings::class, $settings);
        $invalidator = $this->mock(SettingCacheInvalidator::class);
        $invalidator->shouldNotReceive('afterUpdated');

        try {
            app(UpdateSettingAction::class)->executeOrFail('productLandings', 'ultimate', ['brand' => 'PGL']);
            self::fail('The storage exception must reach the caller.');
        } catch (RuntimeException $exception) {
            self::assertSame($failure, $exception);
        }
    }

    public function test_existing_boolean_callers_still_receive_false_on_failure(): void
    {
        $settings = $this->mock(ProductLandingsSettings::class, function ($mock): void {
            $mock->shouldReceive('getAllowedKeys')->once()->andReturn(['ultimate']);
            $mock->shouldReceive('set')->once()->andThrow(new RuntimeException('Storage failure'));
        });
        $this->app->instance('\\'.ProductLandingsSettings::class, $settings);
        $this->mock(SettingCacheInvalidator::class)->shouldNotReceive('afterUpdated');
        Log::shouldReceive('error')->with('Setting persist failed', \Mockery::on(
            fn (array $context): bool => $context['group'] === 'productLandings'
                && $context['key'] === 'ultimate'
                && $context['exception'] === RuntimeException::class
                && $context['message'] === 'Storage failure'
                && isset($context['at']),
        ))->once();

        self::assertFalse(app(UpdateSettingAction::class)->execute('productLandings', 'ultimate', []));
    }
}
