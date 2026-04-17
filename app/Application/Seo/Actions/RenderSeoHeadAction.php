<?php

namespace App\Application\Seo\Actions;

use App\Application\Seo\DTOs\SeoDataDTO;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class RenderSeoHeadAction
{
    public function __construct(
        private readonly Factory $viewFactory,
    )
    {
    }

    public function execute(SeoDataDTO $seoData): View
    {
        return $this->viewFactory->make('front.seo.head', [
            'seoData' => $seoData,
        ]);
    }
}
