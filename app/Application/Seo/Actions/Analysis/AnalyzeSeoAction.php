<?php

namespace App\Application\Seo\Actions\Analysis;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoAnalysisResultDTO;

final class AnalyzeSeoAction
{
    public function __construct(
        protected AnalyzeSeoInputAction $analyzeSeoInputAction,
    )
    {
    }

    public function execute(SeoAnalysisInputDTO $input): SeoAnalysisResultDTO
    {
        return $this->analyzeSeoInputAction->execute($input);
    }
}
