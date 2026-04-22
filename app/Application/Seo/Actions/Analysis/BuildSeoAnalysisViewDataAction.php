<?php

namespace App\Application\Seo\Actions\Analysis;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

final class BuildSeoAnalysisViewDataAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(Model $seoable): array
    {
        if (!method_exists($seoable, 'seoAnalysis')) {
            throw new RuntimeException('The given model does not support seoAnalysis relation.');
        }

        $analysis = $seoable->seoAnalysis;

        if (!$analysis || !is_array($analysis->results)) {
            return [
                'score' => 0,
                'badges' => [
                    'problems' => 0,
                    'improvements' => 0,
                    'good' => 0,
                ],
                'problems' => [],
                'improvements' => [],
                'good' => [],
            ];
        }

        $results = collect($analysis->results['results'] ?? [])
            ->sortByDesc(fn(array $item) => $item['priority'] ?? 0)
            ->values();

        return [
            'score' => (int)$analysis->score,
            'badges' => [
                'problems' => (int)$analysis->problems_count,
                'improvements' => (int)$analysis->improvements_count,
                'good' => (int)$analysis->good_count,
            ],
            'problems' => $results->where('status', 'problem')->values()->all(),
            'improvements' => $results->where('status', 'improvement')->values()->all(),
            'good' => $results->where('status', 'good')->values()->all(),
        ];
    }
}
