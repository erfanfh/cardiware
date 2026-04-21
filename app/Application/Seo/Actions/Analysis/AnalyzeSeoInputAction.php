<?php

namespace App\Application\Seo\Actions\Analysis;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoAnalysisResultDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Application\Seo\Services\SeoCheckRegistry;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class AnalyzeSeoInputAction
{
    public function __construct(
        protected SeoCheckRegistry $seoCheckRegistry,
    )
    {
    }

    public function execute(SeoAnalysisInputDTO $input): SeoAnalysisResultDTO
    {
        $results = [];

        foreach ($this->seoCheckRegistry->checks() as $check) {
            $results[] = $check->analyze($input);
        }

        usort(
            $results,
            static fn(SeoCheckResultDTO $a, SeoCheckResultDTO $b) => $b->priority <=> $a->priority
        );

        $score = array_sum(array_map(static fn(SeoCheckResultDTO $result) => $result->score, $results));
        $maxScore = array_sum(array_map(static fn(SeoCheckResultDTO $result) => $result->maxScore, $results));

        $problemsCount = count(array_filter(
            $results,
            static fn(SeoCheckResultDTO $result) => $result->status === SeoCheckStatusEnum::PROBLEM
        ));

        $improvementsCount = count(array_filter(
            $results,
            static fn(SeoCheckResultDTO $result) => $result->status === SeoCheckStatusEnum::IMPROVEMENT
        ));

        $goodCount = count(array_filter(
            $results,
            static fn(SeoCheckResultDTO $result) => $result->status === SeoCheckStatusEnum::GOOD
        ));

        return new SeoAnalysisResultDTO(
            score: $maxScore > 0 ? (int)round(($score / $maxScore) * 100) : 0,
            maxScore: 100,
            problemsCount: $problemsCount,
            improvementsCount: $improvementsCount,
            goodCount: $goodCount,
            results: $results,
        );
    }
}
