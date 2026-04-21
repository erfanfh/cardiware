<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class SlugStopWordsCheck extends AbstractSeoCheck
{
    /**
     * @var array<int, string>
     */
    protected array $persianStopWords = [
        'از',
        'به',
        'با',
        'برای',
        'در',
        'که',
        'و',
        'یا',
        'را',
        'این',
        'آن',
        'یک',
        'تا',
        'روی',
        'بر',
        'اگر',
        'اما',
        'هم',
        'همه',
        'بین',
        'پس',
        'چون',
        'مثل',
        'بعد',
        'قبل',
        'بدون',
        'توی',
        'تو',
    ];

    /**
     * @var array<int, string>
     */
    protected array $englishStopWords = [
        'a',
        'an',
        'and',
        'are',
        'as',
        'at',
        'be',
        'but',
        'by',
        'for',
        'from',
        'in',
        'into',
        'is',
        'it',
        'no',
        'not',
        'of',
        'on',
        'or',
        'such',
        'that',
        'the',
        'their',
        'then',
        'there',
        'these',
        'they',
        'this',
        'to',
        'was',
        'will',
        'with',
        'your',
    ];

    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'slug_stop_words';
    }

    public function weight(): int
    {
        return 4;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $slug = trim((string)$input->slug);

        if ($slug === '') {
            return $this->makeResult(
                title: 'کلمات اضافی در نامک',
                description: 'تا وقتی نامک مشخص نباشد، بررسی کلمات اضافی آن ممکن نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 60,
            );
        }

        $normalizedSlug = $this->normalizeSlug($slug);
        $slugParts = preg_split('/[-_\s\/]+/u', $normalizedSlug, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($slugParts === []) {
            return $this->makeResult(
                title: 'کلمات اضافی در نامک',
                description: 'نامک برای بررسی کلمات اضافی مناسب نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 60,
            );
        }

        $persianMatchedStopWords = [];
        $englishMatchedStopWords = [];

        foreach ($slugParts as $part) {
            if (in_array($part, $this->persianStopWords, true)) {
                $persianMatchedStopWords[] = $part;
            }

            if (in_array(mb_strtolower($part), $this->englishStopWords, true)) {
                $englishMatchedStopWords[] = mb_strtolower($part);
            }
        }

        $persianMatchedStopWords = array_values(array_unique($persianMatchedStopWords));
        $englishMatchedStopWords = array_values(array_unique($englishMatchedStopWords));

        $matchedStopWords = array_values(array_unique([
            ...$persianMatchedStopWords,
            ...$englishMatchedStopWords,
        ]));

        $matchedCount = count($matchedStopWords);

        if ($matchedCount === 0) {
            return $this->makeResult(
                title: 'کلمات اضافی در نامک',
                description: 'نامک شما کلمات اضافی رایج فارسی یا انگلیسی ندارد. خیلی خوب است.',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'stop_word_count' => 0,
                    'matched_stop_words' => [],
                    'matched_persian_stop_words' => [],
                    'matched_english_stop_words' => [],
                ],
                priority: 60,
            );
        }

        if ($matchedCount === 1) {
            return $this->makeResult(
                title: 'کلمات اضافی در نامک',
                description: 'یک کلمه اضافی رایج در نامک دیده شد. بهتر است در صورت امکان نامک را کوتاه‌تر و تمیزتر کنید.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'stop_word_count' => $matchedCount,
                    'matched_stop_words' => $matchedStopWords,
                    'matched_persian_stop_words' => $persianMatchedStopWords,
                    'matched_english_stop_words' => $englishMatchedStopWords,
                ],
                priority: 60,
            );
        }

        return $this->makeResult(
            title: 'کلمات اضافی در نامک',
            description: 'چند کلمه اضافی رایج فارسی یا انگلیسی در نامک دیده شد. بهتر است نامک را کوتاه‌تر و متمرکزتر کنید.',
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'stop_word_count' => $matchedCount,
                'matched_stop_words' => $matchedStopWords,
                'matched_persian_stop_words' => $persianMatchedStopWords,
                'matched_english_stop_words' => $englishMatchedStopWords,
            ],
            priority: 60,
        );
    }

    protected function normalizeSlug(string $slug): string
    {
        $decodedSlug = urldecode($slug);
        $normalizedSlug = $this->persianTextNormalizer->normalize($decodedSlug);

        return mb_strtolower(trim($normalizedSlug));
    }
}
