<?php

namespace App\Http\Controllers\Front\Poll;

use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Poll\Models\Poll;
use App\Domain\Poll\Models\PollOption;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class PollIndexController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'in:all,active,upcoming,ended'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $status = $validated['status'] ?? 'all';
        $search = trim($validated['search'] ?? '');
        $page = max(1, Paginator::resolveCurrentPage());
        $cache = app(PublicReadCache::class);
        $stats = $cache->remember(
            'poll-stats', [], [Poll::class], fn () => $this->stats(),
            [Poll::class => ['starts_at', 'ends_at']],
        );
        $polls = $cache->paginate(
            'poll-list', [$status, $search, $page], [Poll::class, PollOption::class],
            fn () => $this->polls($status, $search, $page),
            [Poll::class => ['starts_at', 'ends_at']],
        )->withQueryString();

        return view('front.polls.index', compact('polls', 'stats', 'status', 'search'));
    }

    private function stats(): array
    {
        $now = now();
        $base = Poll::query()->publiclyVisible();

        return [
            'all' => (clone $base)->count(),
            'active' => (clone $base)
                ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $now))
                ->count(),
            'upcoming' => (clone $base)->where('starts_at', '>', $now)->count(),
            'ended' => (clone $base)->whereNotNull('ends_at')->where('ends_at', '<=', $now)->count(),
        ];
    }

    private function polls(string $status, string $search, int $page): LengthAwarePaginator
    {
        $now = now();
        $query = Poll::query()->publiclyVisible()->withCount('activeOptions');

        if ($search !== '') {
            $query->where(fn (Builder $builder) => $builder
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('question', 'like', '%'.$search.'%'));
        }

        match ($status) {
            'active' => $query
                ->where(fn (Builder $builder) => $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn (Builder $builder) => $builder->whereNull('ends_at')->orWhere('ends_at', '>', $now)),
            'upcoming' => $query->where('starts_at', '>', $now),
            'ended' => $query->whereNotNull('ends_at')->where('ends_at', '<=', $now),
            default => null,
        };

        return $query
            ->orderByRaw('CASE WHEN (starts_at IS NULL OR starts_at <= ?) AND (ends_at IS NULL OR ends_at > ?) THEN 0 WHEN starts_at > ? THEN 1 ELSE 2 END', [$now, $now, $now])
            ->latest('created_at')
            ->latest('id')
            ->paginate(9, ['*'], 'page', $page);
    }
}
