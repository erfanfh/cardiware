<?php

namespace App\Application\Marketplace\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateStoreAction
{
    public function __construct(
        protected AssignSlugAction $assignSlugAction,
    )
    {
    }

    /**
     * @param Store $store
     * @param array $data
     * @return Store
     * @throws Throwable
     */
    public function execute(Store $store, array $data): Store
    {
        $data['title'] = $data['title'] ?: 'بدون عنوان';
        $data['body'] = $data['body'] ?: '';
        $data['slug'] = $data['slug'] ?: 'untitled-page';

        $store = DB::transaction(function () use ($store, $data) {
            $storeData = $data;

            unset(
                $storeData['slug'],
            );

            $store->update($storeData);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::STORE,
                    model: $store,
                    desiredSlug: $desiredSlug,
                )
            );

            return $store;
        });

        return $store;
    }
}
