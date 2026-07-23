<?php

namespace App\Application\Organization\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class TeamMemberListing extends BaseListing
{
    /**
     * @return array<int, string>
     */
    public function filters(): array
    {
        return [
            'id',
            'first_name',
            'last_name',
            'job_title',
            'mobile',
            'email',
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
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'created_at' => 'created_at',
        ];
    }

    protected function filterId(mixed $value): void
    {
        $this->query->where('id', $value);
    }

    protected function filterFirstName(mixed $value): void
    {
        $this->query->where(
            'first_name',
            'like',
            '%' . $value . '%'
        );
    }

    protected function filterLastName(mixed $value): void
    {
        $this->query->where(
            'last_name',
            'like',
            '%' . $value . '%'
        );
    }

    protected function filterJobTitle(mixed $value): void
    {
        $this->query->where(
            'job_title',
            'like',
            '%' . $value . '%'
        );
    }

    protected function filterMobile(mixed $value): void
    {
        $this->query->where(
            'mobile',
            'like',
            '%' . $value . '%'
        );
    }

    protected function filterEmail(mixed $value): void
    {
        $this->query->where(
            'email',
            'like',
            '%' . $value . '%'
        );
    }

    protected function filterCreatedAtFrom(mixed $value): void
    {
        $this->query->whereDate(
            'created_at',
            '>=',
            $value
        );
    }

    protected function filterCreatedAtTo(mixed $value): void
    {
        $this->query->whereDate(
            'created_at',
            '<=',
            $value
        );
    }
}
