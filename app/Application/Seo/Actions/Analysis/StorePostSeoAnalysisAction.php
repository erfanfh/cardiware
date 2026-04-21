<?php

namespace App\Application\Seo\Actions\Analysis;

use App\Domain\Content\Models\Post;
use Illuminate\Support\Carbon;

final class StorePostSeoAnalysisAction
{
    public function __construct(
        protected AnalyzePostSeoAction $analyzePostSeoAction,
    )
    {
    }

    public function execute(Post $post): void
    {
        $result = $this->analyzePostSeoAction->execute($post);

        $post->seoAnalysis()->updateOrCreate(
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
