<?php

namespace App\Providers;

use App\Application\Financial\Gateways\ZibalPaymentGateway;
use App\Application\Financial\Support\PaymentGatewayManager;
use Illuminate\Support\ServiceProvider;

class FinancialServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayManager::class, function () {
            return new PaymentGatewayManager([
                app(ZibalPaymentGateway::class),
            ]);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
