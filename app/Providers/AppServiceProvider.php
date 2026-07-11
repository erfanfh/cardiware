<?php

namespace App\Providers;

use App\Application\Support\Observers\TicketObserver;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\Order;
use App\Domain\Support\Models\Ticket;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') &&
            class_exists(TelescopeApplicationServiceProvider::class)
        ) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production'))
            URL::forceScheme('https');

        if (Request::is('admin/*'))
            Paginator::defaultView('vendor.pagination.admin');
        else {
            Paginator::defaultView('vendor.pagination.front');
        }

        Relation::morphMap([
            'product' => Product::class,
            'product_sale_item' => ProductSaleItem::class,
            'product_addon' => ProductAddon::class,
            'order' => Order::class,
        ]);

        RateLimiter::for('api', function (\Illuminate\Http\Request $request): Limit {
            $key = $request->user()
                ? 'user:' . $request->user()->getAuthIdentifier()
                : 'ip:' . $request->ip();

            return Limit::perMinute(60)->by($key);
        });

        RateLimiter::for('api-login', function (\Illuminate\Http\Request $request) {
            $login = (string)$request->input('login');

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(5)->by($login . '|' . $request->ip()),
            ];
        });

        Ticket::observe(TicketObserver::class);
    }
}
