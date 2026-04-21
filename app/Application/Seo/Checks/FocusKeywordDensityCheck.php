<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class FocusKeywordDensityCheck extends AbstractSeoCheck
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'focus_keyword_density';
    }

    public function weight(): int
    {
        return 8;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);
        $content = trim((string)$input->plainTextContent);
        $wordCount = max(0, (int)$input->wordCount);

        if ($keyword === '') {
            return $this->makeResult(
                title: 'تراکم عبارت کلیدی',
                description: 'تا وقتی کلیدواژه کانونی تعیین نشده باشد، تراکم آن قابل بررسی نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 87,
            );
        }

        if ($content === '' || $wordCount === 0) {
            return $this->makeResult(
                title: 'تراکم عبارت کلیدی',
                description: 'محتوای کافی برای بررسی تراکم عبارت کلیدی در دسترس نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 87,
            );
        }

        $normalizedContent = $this->persianTextNormalizer->normalize($content);
        $normalizedKeyword = $this->persianTextNormalizer->normalize($keyword);

        $occurrences = $this->countOccurrences($normalizedContent, $normalizedKeyword);
        $density = ($occurrences / $wordCount) * 100;

        if ($density < 0.5) {
            return $this->makeResult(
                title: 'تراکم عبارت کلیدی',
                description: "عبارت کلیدی {$occurrences} بار در متن پیدا شد. بهتر است کمی بیشتر از آن استفاده کنید.",
                status: SeoCheckStatusEnum::PROBLEM,
                meta: [
                    'occurrences' => $occurrences,
                    'word_count' => $wordCount,
                    'density' => round($density, 2),
                ],
                priority: 87,
            );
        }

        if ($density <= 3.0) {
            return $this->makeResult(
                title: 'تراکم عبارت کلیدی',
                description: "عبارت کلیدی {$occurrences} بار پیدا شد. این عالی است!",
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'occurrences' => $occurrences,
                    'word_count' => $wordCount,
                    'density' => round($density, 2),
                ],
                priority: 87,
            );
        }

        if ($density <= 4.5) {
            return $this->makeResult(
                title: 'تراکم عبارت کلیدی',
                description: "عبارت کلیدی {$occurrences} بار پیدا شد. کمی بیش از حد تکرار شده و بهتر است متعادل‌تر شود.",
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'occurrences' => $occurrences,
                    'word_count' => $wordCount,
                    'density' => round($density, 2),
                ],
                priority: 87,
            );
        }

        return $this->makeResult(
            title: 'تراکم عبارت کلیدی',
            description: "عبارت کلیدی {$occurrences} بار پیدا شد و بیش از حد تکرار شده است. بهتر است از تکرار بی‌رویه آن خودداری کنید.",
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'occurrences' => $occurrences,
                'word_count' => $wordCount,
                'density' => round($density, 2),
            ],
            priority: 87,
        );
    }

    protected function countOccurrences(string $content, string $keyword): int
    {
        if ($content === '' || $keyword === '') {
            return 0;
        }

        $pattern = '/(?<!\p{L})' . preg_quote($keyword, '/') . '(?!\p{L})/u';

        preg_match_all($pattern, $content, $matches);

        return count($matches[0] ?? []);
    }
}
