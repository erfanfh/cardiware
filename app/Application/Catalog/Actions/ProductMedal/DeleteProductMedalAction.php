<?php

namespace App\Application\Catalog\Actions\ProductMedal;

use App\Domain\Catalog\Models\ProductMedal;
use Illuminate\Validation\ValidationException;

class DeleteProductMedalAction
{
    /**
     * @throws ValidationException
     */
    public function execute(ProductMedal $medal): void
    {
        if ($medal->assignments()->exists()) {
            throw ValidationException::withMessages([
                'medal' => sprintf(
                    'مدال «%s» در یک یا چند محصول استفاده شده و قابل حذف نیست.',
                    $medal->title
                ),
            ]);
        }

        $medal->delete();
    }
}
