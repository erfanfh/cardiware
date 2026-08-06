<?php

namespace App\Application\Icon\Actions;

use App\Domain\Icon\Models\Icon;

class ResolveIconDetailsAction
{
    /** @var array<string, array{id: int, title: string, svg: string}|null> */
    protected static array $requestCache = [];

    /**
     * @return array{id: int, title: string, svg: string}|null
     */
    public function execute(?string $title): ?array
    {
        $title = trim((string)$title);

        if ($title === '') {
            return null;
        }

        if (array_key_exists($title, self::$requestCache)) {
            return self::$requestCache[$title];
        }

        $icon = Icon::query()
            ->where('full_name', $title)
            ->first(['id', 'full_name', 'svg']);

        return self::$requestCache[$title] = $icon
            ? [
                'id' => $icon->id,
                'title' => $icon->full_name,
                'svg' => $icon->svg,
            ]
            : null;
    }

    /**
     * @param array<int, string> $titles
     * @return array<string, array{id: int, title: string, svg: string}>
     */
    public function executeMany(array $titles): array
    {
        $titles = collect($titles)
            ->map(fn(mixed $title) => trim((string)$title))
            ->filter()
            ->unique()
            ->values();

        if ($titles->isEmpty()) {
            return [];
        }

        $missingTitles = $titles
            ->reject(fn(string $title) => array_key_exists($title, self::$requestCache))
            ->values();

        if ($missingTitles->isNotEmpty()) {
            $resolved = Icon::query()
                ->whereIn('full_name', $missingTitles)
                ->get(['id', 'full_name', 'svg'])
                ->keyBy('full_name');

            foreach ($missingTitles as $title) {
                $icon = $resolved->get($title);

                self::$requestCache[$title] = $icon
                    ? [
                        'id' => $icon->id,
                        'title' => $icon->full_name,
                        'svg' => $icon->svg,
                    ]
                    : null;
            }
        }

        return $titles
            ->mapWithKeys(function (string $title): array {
                $icon = self::$requestCache[$title] ?? null;

                return $icon ? [$title => $icon] : [];
            })
            ->all();
    }
}
