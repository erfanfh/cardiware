<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\Product;

class SyncProductInstallationStepsAction
{
    public function execute(Product $product, string $body): void
    {
        $installationStep = $product->installationSteps()->first();

        if ($installationStep) {
            $installationStep->update([
                'body' => $body,
                'sort_order' => 0,
            ]);

            $product->installationSteps()->where('id', '!=', $installationStep->id)->delete();

            return;
        }

        $product->installationSteps()->create([
            'body' => $body,
            'sort_order' => 0,
        ]);
    }
}
