<?php

namespace App\Application\Catalog\Actions\ProductAttribute;

use App\Domain\Catalog\Models\ProductAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteProductAttributeAction
{
    /**
     * @param ProductAttribute $attribute
     * @return void
     *
     * @throws Throwable
     */
    public function execute(ProductAttribute $attribute): void
    {
        if ($attribute->assignments()->exists()) {
            throw ValidationException::withMessages([
                'attribute' => 'این ویژگی در محصولات استفاده شده و قابل حذف نیست.',
            ]);
        }

        DB::transaction(function () use ($attribute): void {
            $attribute->values()->get()->each->delete();
            $attribute->delete();
        });
    }
}
