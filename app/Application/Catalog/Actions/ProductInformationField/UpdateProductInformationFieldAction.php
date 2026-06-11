<?php

namespace App\Application\Catalog\Actions\ProductInformationField;

use App\Domain\Catalog\Models\ProductInformationField;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateProductInformationFieldAction
{
    /**
     * @throws Throwable
     */
    public function execute(ProductInformationField $field, array $data): ProductInformationField
    {
        return DB::transaction(function () use ($field, $data) {
            $field->update([
                'title' => trim($data['title']),
            ]);

            return $field->refresh();
        });
    }
}
