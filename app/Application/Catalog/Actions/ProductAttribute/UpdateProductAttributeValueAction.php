<?php

namespace App\Application\Catalog\Actions\ProductAttribute;

use App\Domain\Catalog\Models\ProductAttribute;
use App\Domain\Catalog\Models\ProductAttributeValue;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateProductAttributeValueAction
{
    /**
     * @param ProductAttribute $attribute
     * @param ProductAttributeValue $value
     * @param array<string, mixed> $data
     * @return ProductAttributeValue
     *
     * @throws Throwable
     */
    public function execute(
        ProductAttribute      $attribute,
        ProductAttributeValue $value,
        array                 $data
    ): ProductAttributeValue
    {
        abort_unless($value->product_attribute_id === $attribute->id, 404);

        return DB::transaction(function () use ($value, $data) {
            $value->update([
                'title' => $data['title'],
            ]);

            return $value->refresh();
        });
    }
}
