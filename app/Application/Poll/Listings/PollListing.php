<?php

namespace App\Application\Poll\Listings;

use App\Infrastructure\Support\Listings\BaseListing;
use Illuminate\Database\Eloquent\Builder;

class PollListing extends BaseListing
{
    public function filters(): array
    {
        return [
            'id',
            'search',
            'status',
            'audience',
            'results_visibility',
            'timing',
            'starts_at_from',
            'starts_at_to',
            'ends_at_from',
            'ends_at_to',
            'created_at_from',
            'created_at_to',
        ];
    }

    protected function allowedSorts(): array
    {
        return [
            'id' => 'polls.id',
            'responses_count' => 'polls.responses_count',
            'views_count' => 'polls.views_count',
            'starts_at' => 'polls.starts_at',
            'ends_at' => 'polls.ends_at',
            'created_at' => 'polls.created_at',
        ];
    }

    public function defaultSort(): array
    {
        return ['sort' => 'id', 'direction' => 'desc'];
    }

    public function perPage(): int
    {
        return 20;
    }

    protected function filterId(mixed $value): void
    {
        $this->query->whereKey($value);
    }

    protected function filterSearch(mixed $value): void
    {
        $term = '%'.trim((string) $value).'%';

        $this->query->where(fn (Builder $query) => $query
            ->where('title', 'like', $term)
            ->orWhere('question', 'like', $term)
            ->orWhere('slug', 'like', $term));
    }

    protected function filterStatus(mixed $value): void
    {
        $this->query->where('status', $value);
    }

    protected function filterAudience(mixed $value): void
    {
        $this->query->where('audience', $value);
    }

    protected function filterResultsVisibility(mixed $value): void
    {
        $this->query->where('results_visibility', $value);
    }

    protected function filterTiming(mixed $value): void
    {
        match ($value) {
            'not_started' => $this->query->where('starts_at', '>', now()),
            'accepting_votes' => $this->query
                ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now())),
            'ended' => $this->query->whereNotNull('ends_at')->where('ends_at', '<=', now()),
            'without_end' => $this->query->whereNull('ends_at'),
            default => null,
        };
    }

    protected function filterStartsAtFrom(mixed $value): void
    {
        $this->query->whereDate('starts_at', '>=', $value);
    }

    protected function filterStartsAtTo(mixed $value): void
    {
        $this->query->whereDate('starts_at', '<=', $value);
    }

    protected function filterEndsAtFrom(mixed $value): void
    {
        $this->query->whereDate('ends_at', '>=', $value);
    }

    protected function filterEndsAtTo(mixed $value): void
    {
        $this->query->whereDate('ends_at', '<=', $value);
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
