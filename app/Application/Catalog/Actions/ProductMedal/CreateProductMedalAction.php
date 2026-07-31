<?php

namespace App\Application\Catalog\Actions\ProductMedal;

use App\Domain\Catalog\Models\ProductMedal;

class CreateProductMedalAction
{
    public function execute(array $data): ProductMedal
    {
        return ProductMedal::query()->create([
            'title' => trim((string)$data['title']),
            'picture_id' => (int)$data['picture_id'],
            'is_active' => (bool)$data['is_active'],
        ]);
    }
}
