<?php

namespace App\Application\Catalog\Actions\ProductAttribute;

use App\Domain\Catalog\Models\ProductAttribute;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateProductAttributeAction
{
    /**
     * @param array<string, mixed> $data
     * @return ProductAttribute
     *
     * @throws Throwable
     */
    public function execute(array $data): ProductAttribute
    {
        return DB::transaction(function () use ($data) {
            return ProductAttribute::query()->create([
                'title' => $data['title'],
            ]);
        });
    }
}
