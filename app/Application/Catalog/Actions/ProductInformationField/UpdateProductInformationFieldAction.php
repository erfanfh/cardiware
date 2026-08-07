<?php

namespace App\Application\Catalog\Actions\ProductInformationField;

use App\Application\Shared\Cache\Invalidators\ProductCacheInvalidator;
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
                'icon_id' => $data['iconId'] ?? null,
            ]);

            app(ProductCacheInvalidator::class)->flushProductDetails();

            return $field->refresh();
        });
    }
}
