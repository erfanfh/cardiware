<?php

namespace App\Application\Catalog\Actions\ProductAttribute;

use App\Domain\Catalog\Models\ProductAttribute;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateProductAttributeAction
{
    /**
     * @param ProductAttribute $attribute
     * @param array<string, mixed> $data
     * @return ProductAttribute
     *
     * @throws Throwable
     */
    public function execute(ProductAttribute $attribute, array $data): ProductAttribute
    {
        return DB::transaction(function () use ($attribute, $data) {
            $attribute->update([
                'title' => $data['title'],
            ]);

            return $attribute->refresh();
        });
    }
}
