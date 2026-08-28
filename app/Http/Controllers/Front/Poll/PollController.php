<?php

namespace App\Http\Controllers\Front\Poll;

use App\Application\Poll\Actions\BuildPollResultsAction;
use App\Application\Poll\Actions\RecordPollViewAction;
use App\Application\Poll\Support\PollVoterIdentity;
use App\Domain\Poll\Models\Poll;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function __invoke(
        Request $request,
        string $pollSlug,
        RecordPollViewAction $recordView,
        BuildPollResultsAction $resultsAction,
    ): View {
        $poll = Poll::query()->publiclyVisible()->where('slug', $pollSlug)->with('options')->firstOrFail();
        $request->attributes->set('poll', $poll);
        $identity = PollVoterIdentity::resolve($request->user(), $request->ip());
        $recordView->execute($poll, $identity);
        $response = $poll->responses()->where('identity_key', $identity->key)->with('options')->first();
        $hasVoted = $response !== null;
        $showResults = $poll->canShowResults($hasVoted, (bool) $request->user()?->role?->is_super_admin);
        $options = $poll->options->where('is_active', true)->values();
        if ($poll->randomize_options) {
            $options = $options->shuffle();
        }
        $results = $showResults ? $resultsAction->execute($poll) : null;

        return view('front.polls.show', compact('poll', 'options', 'response', 'hasVoted', 'showResults', 'results'));
    }
}
