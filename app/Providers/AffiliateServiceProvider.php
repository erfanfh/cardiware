<?php

namespace App\Providers;

use App\Application\Affiliate\Listeners\AttributeAffiliateOrderListener;
use App\Application\Affiliate\Listeners\BindReferredUserListener;
use App\Application\Affiliate\Listeners\RevertAffiliateOrderListener;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Events\OrderPaymentRevokedEvent;
use App\Domain\Identity\Events\UserRegisteredEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AffiliateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            UserRegisteredEvent::class,
            BindReferredUserListener::class,
        );

        Event::listen(
            OrderPaidEvent::class,
            AttributeAffiliateOrderListener::class,
        );

        Event::listen(
            OrderPaymentRevokedEvent::class,
            RevertAffiliateOrderListener::class,
        );
    }
}
