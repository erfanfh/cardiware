<?php

namespace App\Application\Seo\Actions\Analysis;

use App\Application\Seo\Contracts\SeoAnalysisInputBuilderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use RuntimeException;

final class StoreSeoAnalysisAction
{
    public function __construct(
        protected AnalyzeSeoAction $analyzeSeoAction,
    )
    {
    }

    public function execute(Model $seoable, SeoAnalysisInputBuilderInterface $inputBuilder): void
    {
        if (!method_exists($seoable, 'seoAnalysis')) {
            throw new RuntimeException('The given model does not support seoAnalysis relation.');
        }

        $input = $inputBuilder->execute($seoable);
        $result = $this->analyzeSeoAction->execute($input);

        $seoable->seoAnalysis()->updateOrCreate(
            [],
            [
                'score' => $result->score,
                'max_score' => $result->maxScore,
                'problems_count' => $result->problemsCount,
                'improvements_count' => $result->improvementsCount,
                'good_count' => $result->goodCount,
                'results' => $result->toArray(),
                'analyzed_at' => Carbon::now(),
            ]
        );
    }
}
