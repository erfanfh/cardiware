<?php

namespace App\Application\Icon\Actions;

use App\Domain\Icon\Models\Icon;

class ResolveIconSvgAction
{
    /** @var array<string, string|null> */
    protected static array $requestCache = [];

    public function execute(?string $name): ?string
    {
        $name = trim((string)$name);

        if ($name === '') {
            return null;
        }

        if (array_key_exists($name, self::$requestCache)) {
            return self::$requestCache[$name];
        }

        return self::$requestCache[$name] = Icon::query()
            ->where('full_name', $name)
            ->value('svg');
    }

    /**
     * @param array<int, string> $names
     * @return array<string, string>
     */
    public function executeMany(array $names): array
    {
        $names = collect($names)
            ->map(fn(mixed $name) => trim((string)$name))
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            return [];
        }

        $missingNames = $names
            ->reject(fn(string $name) => array_key_exists($name, self::$requestCache))
            ->values();

        if ($missingNames->isNotEmpty()) {
            $resolved = Icon::query()
                ->whereIn('full_name', $missingNames)
                ->pluck('svg', 'full_name');

            foreach ($missingNames as $name) {
                self::$requestCache[$name] = $resolved->get($name);
            }
        }

        return $names
            ->mapWithKeys(fn(string $name) => self::$requestCache[$name]
                ? [$name => self::$requestCache[$name]]
                : [])
            ->all();
    }
}
