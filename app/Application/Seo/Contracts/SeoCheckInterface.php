<?php

namespace App\Application\Seo\Contracts;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;

interface SeoCheckInterface
{
    public function key(): string;

    public function weight(): int;

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO;
}
