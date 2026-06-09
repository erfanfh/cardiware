<?php

namespace App\Application\Catalog\Actions;

use App\Application\Catalog\Actions\ProductInformation\SyncProductInformationAction;
use App\Application\Seo\Actions\Analysis\BuildProductSeoAnalysisInputAction;
use App\Application\Seo\Actions\Analysis\StoreSeoAnalysisAction;
use App\Application\Seo\Actions\SyncSeoMetaAction;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Taxonomy\Actions\SyncModelProductCategoriesAction;
use App\Application\Taxonomy\Actions\SyncModelProductTagsAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProductAction
{
    public function __construct(
        protected AssignSlugAction                   $assignSlugAction,
        protected SyncModelProductTagsAction         $syncModelProductTagsAction,
        protected SyncModelProductCategoriesAction   $syncModelProductCategoriesAction,
        protected SyncProductImageGalleryAction      $syncProductImageGalleryAction,
        protected SyncProductRequirementsAction      $syncProductRequirementsAction,
        protected SyncProductInstallationStepsAction $syncProductInstallationStepsAction,
        protected SyncProductFaqsAction              $syncProductFaqsAction,
        protected SyncProductReleasesAction          $syncProductReleasesAction,
        protected SyncProductInformationAction       $syncProductInformationAction,
        protected SyncProductAddonsAction            $syncProductAddonsAction,
        protected SyncSeoMetaAction                  $syncSeoMetaAction,
        protected StoreSeoAnalysisAction             $storeSeoAnalysisAction,
    )
    {
    }

    /**
     * Create a new product, assign its slug, and sync related data.
     *
     * @param array $data
     * @return Product
     * @throws Throwable
     */
    public function execute(array $data): Product
    {
        $data['title'] = $data['title'] ?: 'بدون عنوان';
        $data['body'] = $data['body'] ?: '';
        $data['slug'] = $data['slug'] ?: 'untitled-page';

        $product = DB::transaction(function () use ($data) {
            $productData = $data;

            unset(
                $productData['slug'],
                $productData['tags'],
                $productData['category_ids'],
                $productData['image_gallery_items'],
                $productData['requirements'],
                $productData['installation_steps'],
                $productData['faqs'],
                $productData['releases'],
                $productData['attributes'],
                $productData['custom_attributes'],
                $productData['sale_items'],
                $productData['addons'],
                $productData['seo'],
            );

            $product = Product::create($productData);

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
            $this->syncProductInstallationStepsAction->execute($product, $data['installation_steps'] ?? []);
            $this->syncProductFaqsAction->execute($product, $data['faqs'] ?? []);
            $this->syncProductReleasesAction->execute($product, $data['releases'] ?? []);
            $this->syncProductInformationAction->execute($product, $data);
            $this->syncProductAddonsAction->execute($product, $data['addons'] ?? []);

            $this->syncSeoMetaAction->execute($product, $data['seo'] ?? []);
            $this->storeSeoAnalysisAction->execute($product, app(BuildProductSeoAnalysisInputAction::class));

            return $product;
        });

        return $product;
    }
}
