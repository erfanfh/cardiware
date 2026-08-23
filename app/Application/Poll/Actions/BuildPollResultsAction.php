<?php

namespace App\Application\Poll\Actions;

use App\Domain\Poll\Models\Poll;

class BuildPollResultsAction
{
    /** @return array{responses:int,selections:int,options:array<int,array<string,mixed>>} */
    public function execute(Poll $poll): array
    {
        $options = $poll->options()->get();
        $responses = (int) $poll->responses_count;
        $selections = (int) $options->sum('responses_count');

        return [
            'responses' => $responses,
            'selections' => $selections,
            'options' => $options->map(fn ($option) => [
                'id' => $option->id,
                'label' => $option->label,
                'description' => $option->description,
                'color' => $option->color ?: '#f97316',
                'count' => (int) $option->responses_count,
                'respondent_percentage' => $responses > 0 ? round(($option->responses_count / $responses) * 100, 1) : 0.0,
                'selection_percentage' => $selections > 0 ? round(($option->responses_count / $selections) * 100, 1) : 0.0,
            ])->all(),
        ];
    }
}
