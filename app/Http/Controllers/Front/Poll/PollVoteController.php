<?php

namespace App\Http\Controllers\Front\Poll;

use App\Application\Poll\Actions\SubmitPollVoteAction;
use App\Application\Poll\Support\PollVoterIdentity;
use App\Domain\Poll\Models\Poll;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Poll\SubmitPollVoteRequest;
use Illuminate\Http\RedirectResponse;

class PollVoteController extends Controller
{
    public function __invoke(SubmitPollVoteRequest $request, string $pollSlug, SubmitPollVoteAction $action): RedirectResponse
    {
        $poll = Poll::query()->publiclyVisible()->where('slug', $pollSlug)->firstOrFail();
        $identity = PollVoterIdentity::resolve($request->user(), $request->ip());
        $action->execute(
            $poll,
            $identity,
            $request->validated('option_ids'),
            $request->userAgent(),
            $request->headers->get('referer'),
        );

        return redirect()->route('polls.show', $poll->slug)->with('success', 'رأی شما با موفقیت ثبت شد. ممنون که مشارکت کردید.');
    }
}
