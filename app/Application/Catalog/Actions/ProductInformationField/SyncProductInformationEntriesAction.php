<?php

namespace App\Application\Catalog\Actions\ProductInformationField;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductInformationEntry;
use App\Domain\Catalog\Models\ProductInformationField;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncProductInformationEntriesAction
{
    /**
     * Sync product information entries.
     *
     * Empty values will be deleted and will not be stored.
     *
     * @param array<int|string, mixed> $informationFields
     *
     * @throws Throwable
     */
    public function execute(Product $product, array $informationFields = []): void
    {
        DB::transaction(function () use ($product, $informationFields) {
            $validFieldIds = ProductInformationField::query()
                ->whereIn('id', array_keys($informationFields))
                ->pluck('id')
                ->map(fn($id) => (int)$id)
                ->all();

            foreach ($validFieldIds as $fieldId) {
                $value = $informationFields[$fieldId] ?? null;

                if (is_string($value)) {
                    $value = trim($value);
                }

                if (blank($value)) {
                    ProductInformationEntry::query()
                        ->where('product_id', $product->id)
                        ->where('product_information_field_id', $fieldId)
                        ->delete();

                    continue;
                }

                ProductInformationEntry::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'product_information_field_id' => $fieldId,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }

            ProductInformationEntry::query()
                ->where('product_id', $product->id)
                ->whereNotIn('product_information_field_id', $validFieldIds)
                ->delete();
        });
    }
}
