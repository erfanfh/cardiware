<?php

namespace App\Application\Seo\Support;

final class BreadcrumbTrail
{
    public function __construct(private readonly SeoManager $seoManager) {}

    /** @return array<int, array{name: string, url: ?string}> */
    public function items(): array
    {
        $schemaJson = $this->seoManager->resolveCurrent()->get()?->schemaJson;

        if (! is_string($schemaJson) || $schemaJson === '') {
            return [];
        }

        $schema = json_decode($schemaJson, true);

        if (! is_array($schema)) {
            return [];
        }

        $nodes = isset($schema['@graph']) && is_array($schema['@graph'])
            ? $schema['@graph']
            : [$schema];

        foreach ($nodes as $node) {
            if (! is_array($node) || ($node['@type'] ?? null) !== 'BreadcrumbList') {
                continue;
            }

            $items = [];

            foreach ($node['itemListElement'] ?? [] as $item) {
                if (! is_array($item) || ! is_string($item['name'] ?? null)) {
                    continue;
                }

                $name = trim($item['name']);

                if ($name === '') {
                    continue;
                }

                $items[] = [
                    'name' => $name,
                    'url' => is_string($item['item'] ?? null) ? $item['item'] : null,
                ];
            }

            return $items;
        }

        return [];
    }
}
