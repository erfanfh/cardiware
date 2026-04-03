<?php

namespace App\Application\Content\Services;

final class ReservedSlugService
{
    /**
     * @return array<int, string>
     */
    public function all(): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn(mixed $slug): string => trim((string)$slug),
                        config('content.reserved_slugs', [])
                    )
                )
            )
        );
    }

    public function isReserved(string $slug): bool
    {
        return in_array(trim($slug), $this->all(), true);
    }
}
