<?php

namespace App\Http\Controllers\Admin\Poll;

use App\Application\Poll\Actions\CreatePollAction;
use App\Application\Poll\Actions\UpdatePollAction;
use App\Application\Poll\Listings\PollListing;
use App\Domain\Poll\Enums\PollAudienceEnum;
use App\Domain\Poll\Enums\PollResultsVisibilityEnum;
use App\Domain\Poll\Enums\PollStatusEnum;
use App\Domain\Poll\Models\Poll;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Poll\IndexPollRequest;
use App\Http\Requests\Admin\Poll\SavePollRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PollController extends Controller
{
    public function index(IndexPollRequest $request, PollListing $listing): View
    {
        Gate::authorize('poll.list');
        $filters = $request->validated();
        $polls = $listing
            ->handle(
                Poll::query()->withCount([
                    'options as active_options_count' => fn ($query) => $query->where('is_active', true),
                ]),
                $filters,
            )
            ->paginate($listing->perPage())
            ->withQueryString();

        $statuses = collect(PollStatusEnum::cases())
            ->mapWithKeys(fn (PollStatusEnum $status) => [$status->value => $status->label()])
            ->all();
        $audiences = collect(PollAudienceEnum::cases())
            ->mapWithKeys(fn (PollAudienceEnum $audience) => [$audience->value => $audience->label()])
            ->all();
        $resultsVisibilities = collect(PollResultsVisibilityEnum::cases())
            ->mapWithKeys(fn (PollResultsVisibilityEnum $visibility) => [$visibility->value => $visibility->label()])
            ->all();
        $timings = [
            'not_started' => 'شروع‌نشده',
            'accepting_votes' => 'در حال دریافت رأی',
            'ended' => 'پایان‌یافته',
            'without_end' => 'بدون تاریخ پایان',
        ];
        $hasActiveFilters = $listing->hasActiveFilters($filters);

        return view('admin.poll.polls.index', compact(
            'polls',
            'statuses',
            'audiences',
            'resultsVisibilities',
            'timings',
            'hasActiveFilters',
        ));
    }

    public function create(): View
    {
        Gate::authorize('poll.create');

        return view('admin.poll.polls.create');
    }

    public function store(SavePollRequest $request, CreatePollAction $action): RedirectResponse
    {
        Gate::authorize('poll.create');
        $poll = $action->execute($request->validated(), $request->user());

        return redirect()->route('admin.polls.edit', $poll)->with('success', 'نظرسنجی با موفقیت ایجاد شد.');
    }

    public function show(Poll $poll): RedirectResponse
    {
        Gate::authorize('poll.analytics');

        return redirect()->route('admin.polls.analytics', $poll);
    }

    public function edit(Poll $poll): View
    {
        Gate::authorize('poll.edit');
        $poll->load('options');

        return view('admin.poll.polls.edit', compact('poll'));
    }

    public function update(SavePollRequest $request, Poll $poll, UpdatePollAction $action): RedirectResponse
    {
        Gate::authorize('poll.edit');
        $action->execute($poll, $request->validated());

        return redirect()->route('admin.polls.edit', $poll)->with('success', 'نظرسنجی با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Poll $poll): RedirectResponse
    {
        Gate::authorize('poll.delete');
        $poll->delete();

        return redirect()->route('admin.polls.index')->with('success', 'نظرسنجی حذف شد.');
    }
}
