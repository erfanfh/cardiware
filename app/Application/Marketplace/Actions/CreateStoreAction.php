<?php

namespace App\Application\Marketplace\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateStoreAction
{
    public function __construct(
        protected AssignSlugAction $assignSlugAction,
    )
    {
    }

    /**
     * Create a new store, assign its slug, and sync related data.
     *
     * @param array $data
     * @return Store
     * @throws Throwable
     */
    public function execute(array $data): Store
    {
        $data['title'] = $data['title'] ?: 'بدون عنوان';
        $data['body'] = $data['body'] ?: '';
        $data['slug'] = $data['slug'] ?: 'untitled-page';

        $store = DB::transaction(function () use ($data) {
            $storeData = $data;

            unset(
                $storeData['slug'],
                $storeData['seo'],
            );

            $store = Store::create($storeData);

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
