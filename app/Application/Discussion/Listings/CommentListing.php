<?php

namespace App\Application\Discussion\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class CommentListing extends BaseListing
{
    /**
     * @return array<int, string>
     */
    public function filters(): array
    {
        return [
            'id',
            'author_name',
            'author_email',
            'ip_address',
            'content',
            'status',
            'type',
            'created_at_from',
            'created_at_to',
            'post_id'
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function allowedSorts(): array
    {
        return [
            'created_at' => 'created_at',
            'id' => 'id',
        ];
    }

    protected function filterId(mixed $value): void
    {
        $this->query->where('id', $value);
    }

    protected function filterAuthorName(mixed $value): void
    {
        $this->query->where('author_name', 'like', '%' . $value . '%');
    }

    protected function filterAuthorEmail(mixed $value): void
    {
        $this->query->where('author_email', 'like', '%' . $value . '%');
    }

    protected function filterIpAddress(mixed $value): void
    {
        $this->query->where('ip_address', 'like', '%' . $value . '%');
    }

    protected function filterContent(mixed $value): void
    {
        $this->query->where('content', 'like', '%' . $value . '%');
    }

    protected function filterStatus(mixed $value): void
    {
        $this->query->where('status', $value);
    }

    protected function filterType(mixed $value): void
    {
        if ($value === 'root') {
            $this->query->whereNull('parent_id');
            return;
        }

        if ($value === 'reply') {
            $this->query->whereNotNull('parent_id');
        }
    }

    protected function filterCreatedAtFrom(mixed $value): void
    {
        $this->query->whereDate('created_at', '>=', $value);
    }

    protected function filterCreatedAtTo(mixed $value): void
    {
        $this->query->whereDate('created_at', '<=', $value);
    }

    protected function filterPostId(mixed $value): void
    {
        $this->query->whereHas('commentable', function ($query) use ($value) {
            $query->where('id', $value);
        });
    }
}
