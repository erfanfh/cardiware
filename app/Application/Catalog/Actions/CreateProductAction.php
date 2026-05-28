<?php

namespace App\Application\Catalog\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Catalog\Models\Product;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProductAction
{
    public function __construct(
        protected AssignSlugAction $assignSlugAction,
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

            return $product;
        });

        return $product;
    }
}
