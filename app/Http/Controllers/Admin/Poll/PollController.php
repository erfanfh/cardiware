<?php

namespace App\Http\Controllers\Admin\Poll;

use App\Application\Poll\Actions\CreatePollAction;
use App\Application\Poll\Actions\UpdatePollAction;
use App\Domain\Poll\Enums\PollAudienceEnum;
use App\Domain\Poll\Enums\PollStatusEnum;
use App\Domain\Poll\Models\Poll;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Poll\SavePollRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PollController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('poll.list');
        $query = Poll::query()->withCount(['options as active_options_count' => fn ($q) => $q->where('is_active', true)]);

        if (filled($request->string('search'))) {
            $term = '%'.trim($request->string('search')->toString()).'%';
            $query->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('question', 'like', $term));
        }
        if (PollStatusEnum::tryFrom($request->string('status')->toString())) {
            $query->where('status', $request->string('status')->toString());
        }
        if (PollAudienceEnum::tryFrom($request->string('audience')->toString())) {
            $query->where('audience', $request->string('audience')->toString());
        }

        $polls = $query->latest('id')->paginate(20)->withQueryString();

        return view('admin.poll.polls.index', compact('polls'));
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
