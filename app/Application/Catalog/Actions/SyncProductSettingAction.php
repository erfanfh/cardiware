<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncProductSettingAction
{
    /**
     * @throws Throwable
     */
    public function execute(Product $product, array $items = []): void
    {
        DB::transaction(function () use ($product, $items) {
            foreach ($items as $itemKey => $itemValue) {
                $product->displaySetting()->updateOrCreate([], [
                    $itemKey => (bool)$itemValue
                ]);
            }
        });
    }
}
