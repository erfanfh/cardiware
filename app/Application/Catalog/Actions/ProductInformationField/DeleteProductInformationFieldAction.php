<?php

namespace App\Application\Catalog\Actions\ProductInformationField;

use App\Domain\Catalog\Models\ProductInformationField;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteProductInformationFieldAction
{
    /**
     * @throws Throwable
     */
    public function execute(ProductInformationField $field): void
    {
        if ($field->entries()->exists()) {
            throw ValidationException::withMessages([
                'field' => 'این فیلد در اطلاعات محصولات استفاده شده و قابل حذف نیست.',
            ]);
        }

        DB::transaction(function () use ($field) {
            $field->delete();
        });
    }
}
