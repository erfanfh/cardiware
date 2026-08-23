<?php

namespace App\Http\Controllers\Api\V1\Poll;

use App\Application\Poll\Actions\BuildPollResultsAction;
use App\Application\Poll\Actions\RecordPollViewAction;
use App\Application\Poll\Actions\SubmitPollVoteAction;
use App\Application\Poll\Support\PollVoterIdentity;
use App\Domain\Poll\Models\Poll;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Poll\SubmitPollVoteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function show(
        Request $request,
        string $pollSlug,
        RecordPollViewAction $recordView,
        BuildPollResultsAction $resultsAction,
    ): JsonResponse {
        $poll = $this->findPoll($pollSlug);
        $identity = PollVoterIdentity::resolve($request->user(), $request->ip());
        $recordView->execute($poll, $identity);
        $response = $poll->responses()->where('identity_key', $identity->key)->with('options:id')->first();

        return response()->json($this->payload($poll, $response, $resultsAction));
    }

    public function vote(
        SubmitPollVoteRequest $request,
        string $pollSlug,
        SubmitPollVoteAction $submitVote,
        BuildPollResultsAction $resultsAction,
    ): JsonResponse {
        $poll = $this->findPoll($pollSlug);
        $identity = PollVoterIdentity::resolve($request->user(), $request->ip());
        $response = $submitVote->execute(
            $poll,
            $identity,
            $request->validated('option_ids'),
            $request->userAgent(),
            $request->headers->get('referer'),
        );
        $poll->refresh()->load('options');

        return response()->json([
            'message' => 'رأی با موفقیت ثبت شد.',
            'data' => $this->payload($poll, $response, $resultsAction),
        ]);
    }

    private function findPoll(string $slug): Poll
    {
        return Poll::query()->publiclyVisible()->where('slug', $slug)->with('options')->firstOrFail();
    }

    private function payload(Poll $poll, $response, BuildPollResultsAction $resultsAction): array
    {
        $hasVoted = $response !== null;
        $showResults = $poll->canShowResults($hasVoted, false);
        $options = $poll->options->where('is_active', true)->values();
        if ($poll->randomize_options) {
            $options = $options->shuffle();
        }

        return [
            'id' => $poll->public_token,
            'slug' => $poll->slug,
            'title' => $poll->title,
            'question_html' => $poll->question,
            'description_html' => $poll->description,
            'audience' => $poll->audience->value,
            'allows_multiple_selections' => $poll->allows_multiple_selections,
            'min_selections' => $poll->min_selections,
            'max_selections' => $poll->max_selections,
            'allow_vote_changes' => $poll->allow_vote_changes,
            'accepts_votes' => $poll->acceptsVotesAt(),
            'starts_at' => $poll->starts_at?->toIso8601String(),
            'ends_at' => $poll->ends_at?->toIso8601String(),
            'has_voted' => $hasVoted,
            'selected_option_ids' => $response?->options?->pluck('id')->values()->all() ?? [],
            'options' => $options->map(fn ($option) => [
                'id' => $option->id,
                'label' => $option->label,
                'description' => $option->description,
                'color' => $option->color,
            ])->values()->all(),
            'results' => $showResults ? $resultsAction->execute($poll) : null,
        ];
    }
}
