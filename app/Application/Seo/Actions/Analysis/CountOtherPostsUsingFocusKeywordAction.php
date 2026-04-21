<?php

namespace App\Application\Seo\Actions\Analysis;

use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Content\Models\Post;
use App\Domain\Seo\Models\SeoMeta;

final class CountOtherPostsUsingFocusKeywordAction
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function execute(?string $focusKeyword, ?int $exceptPostId = null): int
    {
        $focusKeyword = trim((string)$focusKeyword);

        if ($focusKeyword === '') {
            return 0;
        }

        $normalizedKeyword = $this->persianTextNormalizer->normalize($focusKeyword);

        $count = 0;

        SeoMeta::query()
            ->where('seoable_type', Post::class)
            ->whereNotNull('focus_keyword')
            ->when($exceptPostId, fn($query) => $query->where('seoable_id', '!=', $exceptPostId))
            ->select(['seoable_id', 'focus_keyword'])
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
