<?php

namespace App\Application\Catalog\Actions\ProductInformationField;

use App\Domain\Catalog\Models\ProductInformationField;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProductInformationFieldAction
{
    /**
     * @throws Throwable
     */
    public function execute(array $data): ProductInformationField
    {
        return DB::transaction(function () use ($data) {
            return ProductInformationField::query()->create([
                'title' => trim($data['title']),
                'icon_id' => $data['iconId'] ?? null,
            ]);
        });
    }
}
