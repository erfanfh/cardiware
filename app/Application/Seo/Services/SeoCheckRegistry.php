<?php

namespace App\Application\Seo\Services;

use App\Application\Seo\Checks\ContentLengthCheck;
use App\Application\Seo\Checks\FocusKeywordInMetaDescriptionCheck;
use App\Application\Seo\Checks\FocusKeywordInSeoTitleCheck;
use App\Application\Seo\Checks\FocusKeywordInSlugCheck;
use App\Application\Seo\Checks\FocusKeywordLengthCheck;
use App\Application\Seo\Checks\HasFocusKeywordCheck;
use App\Application\Seo\Checks\HasMetaDescriptionCheck;
use App\Application\Seo\Checks\HasSeoTitleCheck;
use App\Application\Seo\Checks\HasTitleCheck;
use App\Application\Seo\Checks\SingleH1Check;
use App\Application\Seo\Contracts\SeoCheckInterface;

final class SeoCheckRegistry
{
    public function __construct(
        protected HasTitleCheck                      $hasTitleCheck,
        protected HasSeoTitleCheck                   $hasSeoTitleCheck,
        protected HasMetaDescriptionCheck            $hasMetaDescriptionCheck,
        protected HasFocusKeywordCheck               $hasFocusKeywordCheck,
        protected FocusKeywordLengthCheck            $focusKeywordLengthCheck,
        protected FocusKeywordInSeoTitleCheck        $focusKeywordInSeoTitleCheck,
        protected FocusKeywordInMetaDescriptionCheck $focusKeywordInMetaDescriptionCheck,
        protected FocusKeywordInSlugCheck            $focusKeywordInSlugCheck,
        protected ContentLengthCheck                 $contentLengthCheck,
        protected SingleH1Check                      $singleH1Check,
    )
    {
    }

    /**
     * @return array<int, SeoCheckInterface>
     */
    public function checks(): array
    {
        return [
            $this->hasTitleCheck,
            $this->hasSeoTitleCheck,
            $this->hasMetaDescriptionCheck,
            $this->hasFocusKeywordCheck,
            $this->focusKeywordLengthCheck,
            $this->focusKeywordInSeoTitleCheck,
            $this->focusKeywordInMetaDescriptionCheck,
            $this->focusKeywordInSlugCheck,
            $this->contentLengthCheck,
            $this->singleH1Check,
        ];
    }
}
