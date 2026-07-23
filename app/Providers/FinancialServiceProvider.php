<?php

namespace App\Providers;

use App\Application\Financial\Gateways\AqayePardakhtPaymentGateway;
use App\Application\Financial\Gateways\PaypingPaymentGateway;
use App\Application\Financial\Gateways\ZarinpalPaymentGateway;
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
                app(ZarinpalPaymentGateway::class),
                app(PaypingPaymentGateway::class),
                app(AqayePardakhtPaymentGateway::class),
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
