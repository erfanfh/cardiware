<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuditServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\AffiliateServiceProvider;
use App\Providers\FinancialServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\LoyaltyServiceProvider;
use App\Providers\NavigationServiceProvider;
use App\Providers\NotificationServiceProvider;
use App\Providers\PageLayoutServiceProvider;
use App\Providers\PublicReadCacheServiceProvider;
use App\Providers\RaffleServiceProvider;
use App\Providers\RouteBindingServiceProvider;
use App\Providers\SchedulerServiceProvider;
use App\Providers\SeoServiceProvider;
use App\Providers\SlugServiceProvider;

return [
    AppServiceProvider::class,
    PublicReadCacheServiceProvider::class,
    AuditServiceProvider::class,
    AuthServiceProvider::class,
    AffiliateServiceProvider::class,
    RouteBindingServiceProvider::class,
    FinancialServiceProvider::class,
    HorizonServiceProvider::class,
    NavigationServiceProvider::class,
    NotificationServiceProvider::class,
    PageLayoutServiceProvider::class,
    LoyaltyServiceProvider::class,
    RaffleServiceProvider::class,
    SchedulerServiceProvider::class,
    SeoServiceProvider::class,
    SlugServiceProvider::class,
];
