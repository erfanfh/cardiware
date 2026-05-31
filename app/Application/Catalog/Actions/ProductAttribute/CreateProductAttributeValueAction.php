<?php

namespace App\Application\Catalog\Actions\ProductAttribute;

use App\Domain\Catalog\Models\ProductAttribute;
use App\Domain\Catalog\Models\ProductAttributeValue;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProductAttributeValueAction
{
    /**
     * @param ProductAttribute $attribute
     * @param array<string, mixed> $data
     * @return ProductAttributeValue
     *
     * @throws Throwable
     */
    public function execute(ProductAttribute $attribute, array $data): ProductAttributeValue
    {
        return DB::transaction(function () use ($attribute, $data) {
            return $attribute->values()->create([
                'title' => $data['title'],
            ]);
        });
    }
}
