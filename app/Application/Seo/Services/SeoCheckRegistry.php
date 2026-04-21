<?php

namespace App\Application\Seo\Services;

use App\Application\Seo\Checks\AvoidH1InContentCheck;
use App\Application\Seo\Checks\CompetingLinksCheck;
use App\Application\Seo\Checks\ContentLengthCheck;
use App\Application\Seo\Checks\FocusKeywordDensityCheck;
use App\Application\Seo\Checks\FocusKeywordInImageAltCheck;
use App\Application\Seo\Checks\FocusKeywordInIntroductionCheck;
use App\Application\Seo\Checks\FocusKeywordInMetaDescriptionCheck;
use App\Application\Seo\Checks\FocusKeywordInSeoTitleCheck;
use App\Application\Seo\Checks\FocusKeywordInSlugCheck;
use App\Application\Seo\Checks\FocusKeywordInSubheadingsCheck;
use App\Application\Seo\Checks\FocusKeywordLengthCheck;
use App\Application\Seo\Checks\HasFocusKeywordCheck;
use App\Application\Seo\Checks\HasImagesCheck;
use App\Application\Seo\Checks\HasMetaDescriptionCheck;
use App\Application\Seo\Checks\HasSeoTitleCheck;
use App\Application\Seo\Checks\HasSubheadingsCheck;
use App\Application\Seo\Checks\HasTitleCheck;
use App\Application\Seo\Checks\ImageAltCoverageCheck;
use App\Application\Seo\Checks\InternalLinksCheck;
use App\Application\Seo\Checks\KeyphraseDistributionCheck;
use App\Application\Seo\Checks\MetaDescriptionLengthCheck;
use App\Application\Seo\Checks\OutboundFollowLinksCheck;
use App\Application\Seo\Checks\OutboundLinkBalanceCheck;
use App\Application\Seo\Checks\OutboundLinksCheck;
use App\Application\Seo\Checks\PreviouslyUsedFocusKeywordCheck;
use App\Application\Seo\Checks\SeoTitleLengthCheck;
use App\Application\Seo\Checks\SlugFormatCheck;
use App\Application\Seo\Checks\SlugLengthCheck;
use App\Application\Seo\Checks\SlugStopWordsCheck;
use App\Application\Seo\Contracts\SeoCheckInterface;

final class SeoCheckRegistry
{
    public function __construct(
        protected HasTitleCheck                      $hasTitleCheck,
        protected HasSeoTitleCheck                   $hasSeoTitleCheck,
        protected SeoTitleLengthCheck                $seoTitleLengthCheck,
        protected HasMetaDescriptionCheck            $hasMetaDescriptionCheck,
        protected MetaDescriptionLengthCheck         $metaDescriptionLengthCheck,
        protected HasFocusKeywordCheck               $hasFocusKeywordCheck,
        protected FocusKeywordLengthCheck            $focusKeywordLengthCheck,

        protected FocusKeywordInSeoTitleCheck        $focusKeywordInSeoTitleCheck,
        protected FocusKeywordInMetaDescriptionCheck $focusKeywordInMetaDescriptionCheck,
        protected FocusKeywordInSlugCheck            $focusKeywordInSlugCheck,
        protected SlugLengthCheck                    $slugLengthCheck,
        protected SlugStopWordsCheck                 $slugStopWordsCheck,
        protected SlugFormatCheck                    $slugFormatCheck,

        protected ContentLengthCheck                 $contentLengthCheck,
        protected HasSubheadingsCheck                $hasSubheadingsCheck,
        protected AvoidH1InContentCheck              $avoidH1InContentCheck,
        protected FocusKeywordInIntroductionCheck    $focusKeywordInIntroductionCheck,
        protected FocusKeywordDensityCheck           $focusKeywordDensityCheck,
        protected KeyphraseDistributionCheck         $keyphraseDistributionCheck,
        protected FocusKeywordInSubheadingsCheck     $focusKeywordInSubheadingsCheck,

        protected InternalLinksCheck                 $internalLinksCheck,
        protected OutboundLinksCheck                 $outboundLinksCheck,
        protected OutboundFollowLinksCheck           $outboundFollowLinksCheck,
        protected OutboundLinkBalanceCheck           $outboundLinkBalanceCheck,
        protected CompetingLinksCheck                $competingLinksCheck,

        protected HasImagesCheck                     $hasImagesCheck,
        protected ImageAltCoverageCheck              $imageAltCoverageCheck,
        protected FocusKeywordInImageAltCheck        $focusKeywordInImageAltCheck,

        protected PreviouslyUsedFocusKeywordCheck    $previouslyUsedFocusKeywordCheck,
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
            $this->seoTitleLengthCheck,
            $this->hasMetaDescriptionCheck,
            $this->metaDescriptionLengthCheck,
            $this->hasFocusKeywordCheck,
            $this->focusKeywordLengthCheck,

            $this->focusKeywordInSeoTitleCheck,
            $this->focusKeywordInMetaDescriptionCheck,
            $this->focusKeywordInSlugCheck,
            $this->slugLengthCheck,
            $this->slugStopWordsCheck,
            $this->slugFormatCheck,

            $this->contentLengthCheck,
            $this->hasSubheadingsCheck,
            $this->avoidH1InContentCheck,
            $this->focusKeywordInIntroductionCheck,
            $this->focusKeywordDensityCheck,
            $this->keyphraseDistributionCheck,
            $this->focusKeywordInSubheadingsCheck,

            $this->internalLinksCheck,
            $this->outboundLinksCheck,
            $this->outboundFollowLinksCheck,
            $this->outboundLinkBalanceCheck,
            $this->competingLinksCheck,

            $this->hasImagesCheck,
            $this->imageAltCoverageCheck,
            $this->focusKeywordInImageAltCheck,

            $this->previouslyUsedFocusKeywordCheck,
        ];
    }
}
