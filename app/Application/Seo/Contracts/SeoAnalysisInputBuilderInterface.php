<?php

namespace App\Application\Seo\Contracts;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use Illuminate\Database\Eloquent\Model;

interface SeoAnalysisInputBuilderInterface
{
    public function execute(Model $model): SeoAnalysisInputDTO;
}
