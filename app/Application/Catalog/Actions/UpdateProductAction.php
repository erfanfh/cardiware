<?php

namespace App\Application\Catalog\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Post;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateProductAction
{
    public function __construct(
        protected AssignSlugAction $assignSlugAction,
    )
    {
    }

    /**
     * @param Product $product
     * @param array $data
     * @return Post
     * @throws Throwable
     */
    public function execute(Product $product, array $data): Post
    {
        $data['title'] = $data['title'] ?: 'بدون عنوان';
        $data['body'] = $data['body'] ?: '';
        $data['slug'] = $data['slug'] ?: 'untitled-page';

        $product = DB::transaction(function () use ($product, $data) {
            $productData = $data;

            unset(
                $productData['slug'],
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

            return $product;
        });

        return $product;
    }
}
