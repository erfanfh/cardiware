<?php

namespace App\Providers;

use App\Application\Catalog\Observers\LauncherContentManifestRevisionObserver;
use App\Application\Catalog\Observers\ProductSaleItemFileObserver;
use App\Application\Catalog\Observers\ProductSaleItemManifestObserver;
use App\Application\Catalog\Observers\ProductSaleItemObserver;
use App\Application\Catalog\Observers\ProductSlugCacheObserver;
use App\Application\Discussion\Actions\ResolveCommentAuthorBadgesAction;
use App\Application\Identity\Observers\UserObserver;
use App\Application\Launcher\Observers\LauncherCacheObserver;
use App\Application\Launcher\Observers\LauncherReleaseObserver;
use App\Application\Marketplace\Observers\StoreSlugCacheObserver;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherGuide;
use App\Domain\Launcher\Models\LauncherNotification;
use App\Domain\Launcher\Models\LauncherSlide;
use App\Application\Support\Observers\TicketObserver;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Catalog\Models\ProductSaleItemManifest;
use App\Domain\Catalog\Models\ProductSlug;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\WalletTopUp;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherModGroup;
use App\Domain\Launcher\Models\LauncherRelease;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Marketplace\Models\StoreSlug;
use App\Domain\Support\Models\Ticket;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Domain\Video\Models\Video;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Queue;
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

        if (class_exists(LoyaltyRaffleIntegrationServiceProvider::class)) {
            $this->app->register(LoyaltyRaffleIntegrationServiceProvider::class);
        }

        $this->app->scoped(
            ResolveCommentAuthorBadgesAction::class,
            fn () => new ResolveCommentAuthorBadgesAction
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Queue::before(function (): void {
            BaseSettings::clearResolvedGroups();
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        if (Request::is('admin/*')) {
            Paginator::defaultView('vendor.pagination.admin');
        } else {
            Paginator::defaultView('vendor.pagination.front');
        }

        Relation::morphMap([
            'vip_plan' => \App\Domain\Vip\Models\VipPlan::class,
            'product' => Product::class,
            'product_sale_item' => ProductSaleItem::class,
            'product_addon' => ProductAddon::class,
            'launcher_mod' => \App\Domain\Launcher\Models\LauncherMod::class,
            'order' => Order::class,
            'wallet_top_up' => WalletTopUp::class,
            'video' => Video::class,
        ]);

        RateLimiter::for('api', function (\Illuminate\Http\Request $request): Limit {
            $key = $request->user()
                ? 'user:'.$request->user()->getAuthIdentifier()
                : 'ip:'.$request->ip();

            return Limit::perMinute(60)->by($key);
        });

        RateLimiter::for('api-login', function (\Illuminate\Http\Request $request) {
            $login = (string) $request->input('login');

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(5)->by($login.'|'.$request->ip()),
            ];
        });

        RateLimiter::for('catalog', function (\Illuminate\Http\Request $request): Limit {
            $key = $request->user()
                ? 'user:'.$request->user()->getAuthIdentifier()
                : 'ip:'.$request->ip();

            return Limit::perMinute(120)->by('catalog:'.$key);
        });

        RateLimiter::for('raffle-claims', function (\Illuminate\Http\Request $request): Limit {
            return Limit::perMinute(6)->by('raffle-claim:'.($request->user()?->getAuthIdentifier() ?: $request->ip()));
        });

        RateLimiter::for('poll-votes', function (\Illuminate\Http\Request $request): Limit {
            return Limit::perMinute(10)->by('poll-vote:'.($request->user()?->getAuthIdentifier() ?: $request->ip()));
        });

        RateLimiter::for(
            'payment-callback',
            function (\Illuminate\Http\Request $request): Limit {
                $reference = $request->input('trackId')
                    ?? $request->input('track_id')
                    ?? $request->input('Authority')
                    ?? $request->input('authority')
                    ?? $request->input('refId')
                    ?? $request->input('RefId')
                    ?? $request->input('referenceId')
                    ?? $request->input('reference_id')
                    ?? $request->input('code')
                    ?? $request->input('Code');

                $gateway = (string) $request->route('gateway');

                $key = filled($reference)
                    ? "{$gateway}:{$reference}"
                    : "missing-reference:{$request->ip()}";

                return Limit::perMinute(10)
                    ->by("payment-callback:{$key}");
            },
        );

        Ticket::observe(TicketObserver::class);
        ProductSaleItem::observe(ProductSaleItemObserver::class);
        ProductSaleItemManifest::observe(ProductSaleItemManifestObserver::class);
        ProductSaleItemFile::observe(ProductSaleItemFileObserver::class);
        Product::observe(ProductSlugCacheObserver::class);
        ProductSlug::observe(ProductSlugCacheObserver::class);
        Store::observe(StoreSlugCacheObserver::class);
        StoreSlug::observe(StoreSlugCacheObserver::class);

        LauncherUpdate::observe(LauncherContentManifestRevisionObserver::class);
        LauncherUpdateFile::observe(LauncherContentManifestRevisionObserver::class);
        LauncherModGroup::observe(LauncherContentManifestRevisionObserver::class);
        LauncherMod::observe(LauncherContentManifestRevisionObserver::class);
        LauncherModFile::observe(LauncherContentManifestRevisionObserver::class);
        LauncherRelease::observe(LauncherReleaseObserver::class);

        // Launcher API read cache -- models whose change is not already a manifest bump.
        LauncherSlide::observe(LauncherCacheObserver::class);
        LauncherNotification::observe(LauncherCacheObserver::class);
        LauncherGuide::observe(LauncherCacheObserver::class);
        LauncherDevice::observe(LauncherCacheObserver::class);
        PurchaseAccess::observe(LauncherCacheObserver::class);
        Product::observe(LauncherCacheObserver::class);

        User::observe(UserObserver::class);
    }
}
