<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Arr;

class SyncProductAddonsAction
{
    /**
     * Sync product addons with submitted form data.
     *
     * @param Product $product
     * @param array<int, array<string, mixed>> $addons
     * @return void
     */
    public function execute(Product $product, array $addons): void
    {
        $submittedAddonIds = collect($addons)
            ->pluck('id')
            ->filter()
            ->map(fn($id) => (int)$id)
            ->values();

        $product->addons()
            ->whereNotIn('id', $submittedAddonIds)
            ->delete();

        foreach (array_values($addons) as $index => $addonData) {
            $this->saveAddon($product, $addonData, $index);
        }
    }

    /**
     * Create or update a single product addon.
     *
     * @param Product $product
     * @param array<string, mixed> $addonData
     * @param int $index
     * @return void
     */
    private function saveAddon(Product $product, array $addonData, int $index): void
    {
        $addonId = Arr::get($addonData, 'id');

        $payload = $this->makePayload($addonData, $index);

        if ($addonId) {
            $product->addons()
                ->whereKey($addonId)
                ->firstOrFail()
                ->update($payload);

            return;
        }

        $product->addons()->create($payload);
    }

    /**
     * Prepare product addon payload for persistence.
     *
     * @param array<string, mixed> $addonData
     * @param int $index
     * @return array<string, mixed>
     */
    private function makePayload(array $addonData, int $index): array
    {
        return [
            'title' => trim((string)Arr::get($addonData, 'title')),
            'description' => filled(Arr::get($addonData, 'description'))
                ? trim((string)Arr::get($addonData, 'description'))
                : null,
            'price' => max(0, (int)Arr::get($addonData, 'price', 0)),
            'is_active' => $this->toBoolean(Arr::get($addonData, 'is_active', true)),
            'sort_order' => $index,
            'metadata' => Arr::get($addonData, 'metadata'),
        ];
    }

    /**
     * Convert form value to boolean.
     *
     * @param mixed $value
     * @return bool
     */
    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
