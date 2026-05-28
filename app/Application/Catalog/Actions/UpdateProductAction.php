<?php

namespace App\Application\Catalog\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Taxonomy\Actions\SyncModelProductCategoriesAction;
use App\Application\Taxonomy\Actions\SyncModelProductTagsAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateProductAction
{
    public function __construct(
        protected AssignSlugAction                 $assignSlugAction,
        protected SyncModelProductTagsAction       $syncModelProductTagsAction,
        protected SyncModelProductCategoriesAction $syncModelProductCategoriesAction,
    )
    {
    }

    /**
     * @param Product $product
     * @param array $data
     * @return Product
     * @throws Throwable
     */
    public function execute(Product $product, array $data): Product
    {
        $data['title'] = $data['title'] ?: 'بدون عنوان';
        $data['body'] = $data['body'] ?: '';
        $data['slug'] = $data['slug'] ?: 'untitled-page';

        $product = DB::transaction(function () use ($product, $data) {
            $productData = $data;

            unset(
                $productData['slug'],
                $productData['tags'],
                $productData['category_ids'],
            );

            $product->update($productData);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::PRODUCT,
                    model: $product,
                    desiredSlug: $desiredSlug,
                )
            );

            $this->syncModelProductTagsAction->execute($product, $data['tags'] ?? []);
            $this->syncModelProductCategoriesAction->execute($product, $data['category_ids'] ?? []);

            return $product;
        });

        return $product;
    }
}
