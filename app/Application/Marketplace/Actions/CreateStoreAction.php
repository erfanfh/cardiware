<?php

namespace App\Application\Marketplace\Actions;

use App\Application\Seo\Actions\Analysis\BuildStoreSeoAnalysisInputAction;
use App\Application\Seo\Actions\Analysis\StoreSeoAnalysisAction;
use App\Application\Seo\Actions\SyncSeoMetaAction;
use App\Application\Shared\Cache\Invalidators\StoreCacheInvalidator;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateStoreAction
{
    public function __construct(
        protected AssignSlugAction $assignSlugAction,
        protected SyncSeoMetaAction $syncSeoMetaAction,
        protected StoreSeoAnalysisAction $storeSeoAnalysisAction,
        protected StoreCacheInvalidator $storeCacheInvalidator,
    ) {}

    /**
     * Create a new store, assign its slug, and sync related data.
     *
     * @throws Throwable
     */
    public function execute(array $data): Store
    {
        $data['title'] = ($data['title'] ?? null) ?: 'بدون عنوان';
        $data['body'] = ($data['body'] ?? null) ?: '';
        $data['slug'] = ($data['slug'] ?? null) ?: 'untitled-page';

        $store = DB::transaction(function () use ($data) {
            $storeData = $data;

            if (array_key_exists('seller_permissions', $storeData)) {
                $storeData['seller_permissions'] = array_map(fn ($enabled) => (bool) $enabled, $storeData['seller_permissions']);
            }

            unset(
                $storeData['slug'],
                $storeData['seo'],
                $storeData['seller_user_ids'],
            );

            $store = Store::create($storeData);

            app(AuditService::class)->sync($store, 'sellers', $data['seller_user_ids'] ?? []);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::STORE,
                    model: $store,
                    desiredSlug: $desiredSlug,
                )
            );

            $this->syncSeoMetaAction->execute($store, $data['seo'] ?? []);
            $this->storeSeoAnalysisAction->execute($store, app(BuildStoreSeoAnalysisInputAction::class));

            return $store;
        });

        $this->storeCacheInvalidator->afterCreated($store);

        return $store;
    }
}
