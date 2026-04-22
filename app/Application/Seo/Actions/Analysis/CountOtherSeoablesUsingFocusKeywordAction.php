<?php

namespace App\Application\Seo\Actions\Analysis;

use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Models\SeoMeta;

final class CountOtherSeoablesUsingFocusKeywordAction
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function execute(
        string  $seoableType,
        ?string $focusKeyword,
        ?int    $exceptSeoableId = null,
    ): int
    {
        $focusKeyword = trim((string)$focusKeyword);

        if ($focusKeyword === '') {
            return 0;
        }

        $normalizedKeyword = $this->persianTextNormalizer->normalize($focusKeyword);
        $count = 0;

        SeoMeta::query()
            ->where('seoable_type', $seoableType)
            ->whereNotNull('focus_keyword')
            ->when($exceptSeoableId, fn($query) => $query->where('seoable_id', '!=', $exceptSeoableId))
            ->select(['id', 'seoable_id', 'focus_keyword'])
            ->chunkById(200, function ($items) use ($normalizedKeyword, &$count) {
                foreach ($items as $item) {
                    $itemKeyword = trim((string)$item->focus_keyword);

                    if ($itemKeyword === '') {
                        continue;
                    }

                    if ($this->persianTextNormalizer->normalize($itemKeyword) === $normalizedKeyword) {
                        $count++;
                    }
                }
            });

        return $count;
    }
}
