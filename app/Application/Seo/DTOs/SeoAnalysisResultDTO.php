<?php

namespace App\Application\Seo\DTOs;

final readonly class SeoAnalysisResultDTO
{
    /**
     * @param array<int, SeoCheckResultDTO> $results
     */
    public function __construct(
        public int   $score,
        public int   $maxScore,
        public int   $problemsCount,
        public int   $improvementsCount,
        public int   $goodCount,
        public array $results,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'max_score' => $this->maxScore,
            'counts' => [
                'problems' => $this->problemsCount,
                'improvements' => $this->improvementsCount,
                'good' => $this->goodCount,
            ],
            'results' => array_map(
                static fn(SeoCheckResultDTO $result) => $result->toArray(),
                $this->results
            ),
        ];
    }
}
