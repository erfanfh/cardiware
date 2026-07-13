<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GetCatalogProductDetailAction
{
    public function __construct(
        private readonly HydrateCatalogProductOwnershipAction $hydrateOwnershipAction,
    )
    {
    }

    public function execute(
        int   $productId,
        ?User $user,
    ): Product
    {
        $relations = [
            'picture',
            'store.picture',

            'categories',
            'tags',

            'imageGallery.mediaFile',
            'videoGallery.mediaFile',

            'requirements',
            'installationSteps',
            'faqs',

            'releases' => function (HasMany $relation): void {
                $relation
                    ->orderByDesc('released_at')
                    ->orderBy('sort_order');
            },

            'releases.releaseChanges' => function (
                HasMany $relation,
            ): void {
                $relation
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },

            'informationEntries.field',

            'attributeAssignments' => function (
                HasMany $relation,
            ): void {
                $relation
                    ->where('is_visible', true)
                    ->orderBy('sort_order');
            },

            'attributeAssignments.attribute',
            'attributeAssignments.assignmentValues.value',

            'saleItems.discount',
            'saleItems.attributeValues.globalValue.attribute',

            'addons' => function (HasMany $relation): void {
                $relation
                    ->where('is_active', true)
                    ->orderBy('sort_order');
            },
        ];

        if ($user !== null) {
            $relations['purchaseAccesses'] = function (
                HasMany $relation,
            ) use ($user): void {
                $relation
                    ->withoutGlobalScopes()
                    ->where('user_id', $user->getKey())
                    ->select([
                        'id',
                        'user_id',
                        'product_id',
                        'product_sale_item_id',
                        'status',
                        'starts_at',
                        'expires_at',
                    ]);
            };
        }

        $product = Product::query()
            ->whereKey($productId)
            ->with($relations)
            ->firstOrFail();

        $this->hydrateOwnershipAction->execute(
            products: collect([$product]),
            user: $user,
        );

        return $product;
    }
}
