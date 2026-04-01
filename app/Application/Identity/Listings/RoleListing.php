<?php

namespace App\Application\Identity\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class RoleListing extends BaseListing
{
    /**
     * @return array<int, string>
     */
    public function filters(): array
    {
        return [
            'id',
            'title',
            'permission_ids',
            'created_at_from',
            'created_at_to',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function allowedSorts(): array
    {
        return [
            'created_at' => 'created_at',
        ];
    }

    protected function filterId(mixed $value): void
    {
        $this->query->where('id', $value);
    }

    protected function filterTitle(mixed $value): void
    {
        $this->query->where('title', 'like', '%' . $value . '%');
    }

    protected function filterPermissionIds(mixed $value): void
    {
        $permissionIds = array_filter((array)$value, static fn($id) => filled($id));

        if ($permissionIds === []) {
            return;
        }

        $this->query->whereHas('permissions', function ($query) use ($permissionIds) {
            $query->whereIn('id', $permissionIds);
        });
    }

    protected function filterCreatedAtFrom(mixed $value): void
    {
        $this->query->whereDate('created_at', '>=', $value);
    }

    protected function filterCreatedAtTo(mixed $value): void
    {
        $this->query->whereDate('created_at', '<=', $value);
    }
}
