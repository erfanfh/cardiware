<?php

namespace App\Application\Catalog\Actions;

use App\Application\Catalog\Actions\ProductInformation\SyncProductInformationAction;
use App\Application\Catalog\Actions\ProductInformationField\SyncProductInformationEntriesAction;
use App\Application\Seo\Actions\Analysis\BuildProductSeoAnalysisInputAction;
use App\Application\Seo\Actions\Analysis\StoreSeoAnalysisAction;
use App\Application\Seo\Actions\SyncSeoMetaAction;
use App\Application\Shared\Cache\Invalidators\ProductCacheInvalidator;
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
        protected AssignSlugAction                    $assignSlugAction,
        protected SyncModelProductTagsAction          $syncModelProductTagsAction,
        protected SyncModelProductCategoriesAction    $syncModelProductCategoriesAction,
        protected SyncProductImageGalleryAction       $syncProductImageGalleryAction,
        protected SyncProductRequirementsAction       $syncProductRequirementsAction,
        protected SyncProductInstallationStepsAction  $syncProductInstallationStepsAction,
        protected SyncProductFaqsAction               $syncProductFaqsAction,
        protected SyncProductReleasesAction           $syncProductReleasesAction,
        protected SyncProductInformationAction        $syncProductInformationAction,
        protected SyncProductAddonsAction             $syncProductAddonsAction,
        protected SyncProductInformationEntriesAction $syncProductInformationEntriesAction,
        protected SyncProductSettingAction            $syncProductSettingAction,
        protected SyncSeoMetaAction                   $syncSeoMetaAction,
        protected StoreSeoAnalysisAction              $storeSeoAnalysisAction,
        protected ProductCacheInvalidator             $productCacheInvalidator,
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
                $productData['image_gallery_items'],
                $productData['requirements'],
                $productData['installation_step_body'],
                $productData['faqs'],
                $productData['releases'],
                $productData['attributes'],
                $productData['custom_attributes'],
                $productData['sale_items'],
                $productData['addons'],
                $productData['information_fields'],
                $productData['seo'],
                $productData['display_setting'],
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
            $this->syncProductImageGalleryAction->execute($product, $data['image_gallery_items'] ?? []);
            $this->syncProductRequirementsAction->execute($product, $data['requirements'] ?? []);
            $this->syncProductInstallationStepsAction->execute($product, $data['installation_step_body'] ?? '');
            $this->syncProductFaqsAction->execute($product, $data['faqs'] ?? []);
            $this->syncProductReleasesAction->execute($product, $data['releases'] ?? []);
            $this->syncProductInformationAction->execute($product, $data);
            $this->syncProductAddonsAction->execute($product, $data['addons'] ?? []);
            $this->syncProductInformationEntriesAction->execute($product, $data['information_fields'] ?? []);
            $this->syncProductSettingAction->execute($product, $data['display_setting'] ?? []);

            $this->syncSeoMetaAction->execute($product, $data['seo'] ?? []);
            $this->storeSeoAnalysisAction->execute($product, app(BuildProductSeoAnalysisInputAction::class));

            return $product;
        });

        $this->productCacheInvalidator->afterUpdated($product);

        return $product;
    }
}
