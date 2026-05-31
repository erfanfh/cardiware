<?php

namespace App\Application\Catalog\Actions\ProductAttribute;

use App\Domain\Catalog\Models\ProductAttribute;
use App\Domain\Catalog\Models\ProductAttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteProductAttributeValueAction
{
    /**
     * @param ProductAttribute $attribute
     * @param ProductAttributeValue $value
     * @return void
     *
     * @throws Throwable
     */
    public function execute(ProductAttribute $attribute, ProductAttributeValue $value): void
    {
        abort_unless($value->product_attribute_id === $attribute->id, 404);

        if ($value->assignmentValues()->exists()) {
            throw ValidationException::withMessages([
                'value' => 'این مقدار در محصولات استفاده شده و قابل حذف نیست.',
            ]);
        }

        DB::transaction(function () use ($value): void {
            $value->delete();
        });
    }
}
