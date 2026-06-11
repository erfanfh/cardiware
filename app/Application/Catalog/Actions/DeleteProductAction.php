<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\Product;

class DeleteProductAction
{
    public function __construct(
//        protected ProjectCacheInvalidator $projectCacheInvalidator,
    )
    {
    }

    /**
     * Delete the given project.
     *
     * @param Product $product
     * @return void
     */
    public function execute(Product $product): void
    {
//        $this->projectCacheInvalidator->afterDeleted($project);

        $product->delete();
    }
}
