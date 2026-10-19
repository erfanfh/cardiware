<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\ProductRelease;
use App\Domain\Catalog\Support\ProductReleaseContent;

class SyncProductReleaseChangesAction
{
    /**
     * @param ProductRelease $release
     * @param mixed $changes
     * @return void
     */
    public function execute(ProductRelease $release, mixed $changes): void
    {
        $items = $this->parseChanges($changes);

        $release->update([
            'changes_body' => ProductReleaseContent::fromChanges(
                array_map(static fn (array $item): object => (object) $item, $items)
            ),
        ]);

        $release->releaseChanges()->get()->each->delete();

        if ($items === []) {
            return;
        }

        $release->releaseChanges()->createMany($items);
    }

    /**
     * @param mixed $changes
     * @return array<int, array{title: string, sort_order: int, is_special: bool}>
     */
    private function parseChanges(mixed $changes): array
    {
        if ($changes === null) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', (string)$changes);

        if ($lines === false) {
            return [];
        }

        $items = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $isSpecial = str_starts_with($line, '**');

            if ($isSpecial) {
                $line = trim(mb_substr($line, 2));
            }

            if ($line === '') {
                continue;
            }

            $items[] = [
                'title' => $line,
                'sort_order' => count($items),
                'is_special' => $isSpecial,
            ];
        }

        return $items;
    }
}
