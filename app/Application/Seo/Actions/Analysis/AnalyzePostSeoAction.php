<?php

namespace App\Application\Seo\Actions\Analysis;

use App\Application\Seo\DTOs\SeoAnalysisResultDTO;
use App\Domain\Content\Models\Post;

final class AnalyzePostSeoAction
{
    public function __construct(
        protected BuildPostSeoAnalysisInputAction $buildPostSeoAnalysisInputAction,
        protected AnalyzeSeoInputAction           $analyzeSeoInputAction,
    )
    {
    }

    public function execute(Post $post): SeoAnalysisResultDTO
    {
        $input = $this->buildPostSeoAnalysisInputAction->execute($post);

        return $this->analyzeSeoInputAction->execute($input);
    }
}
