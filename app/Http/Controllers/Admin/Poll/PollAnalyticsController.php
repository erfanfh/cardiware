<?php

namespace App\Http\Controllers\Admin\Poll;

use App\Application\Poll\Actions\BuildPollResultsAction;
use App\Domain\Poll\Models\Poll;
use App\Domain\Poll\Models\PollResponse;
use App\Domain\Poll\Models\PollView;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class PollAnalyticsController extends Controller
{
    public function __invoke(Poll $poll, BuildPollResultsAction $resultsAction): View
    {
        Gate::authorize('poll.analytics');
        $poll->load('options');
        $results = $resultsAction->execute($poll);
        $registered = $poll->responses()->whereNotNull('user_id')->count();
        $guests = max(0, $poll->responses_count - $registered);
        $conversionRate = $poll->views_count > 0 ? round(($poll->responses_count / $poll->views_count) * 100, 1) : 0;

        $start = now()->subDays(29)->startOfDay();
        $responseDays = PollResponse::query()
            ->where('poll_id', $poll->id)
            ->where('submitted_at', '>=', $start)
            ->selectRaw('DATE(submitted_at) as day, COUNT(*) as aggregate')
            ->groupBy('day')
            ->pluck('aggregate', 'day');
        $viewDays = PollView::query()
            ->where('poll_id', $poll->id)
            ->where('viewed_on', '>=', $start->toDateString())
            ->selectRaw('viewed_on as day, COUNT(*) as aggregate')
            ->groupBy('day')
            ->pluck('aggregate', 'day');
        $timeline = collect(range(0, 29))->map(function (int $offset) use ($start, $responseDays, $viewDays): array {
            $date = $start->copy()->addDays($offset);
            $key = $date->toDateString();

            return ['date' => jalali_date($date, 'm/d'), 'responses' => $responseDays[$key] ?? 0, 'views' => $viewDays[$key] ?? 0];
        })->all();

        $recentResponses = $poll->responses()->with(['user', 'options'])->latest('submitted_at')->paginate(15);

        return view('admin.poll.polls.analytics', compact(
            'poll', 'results', 'registered', 'guests', 'conversionRate', 'timeline', 'recentResponses'
        ));
    }
}
