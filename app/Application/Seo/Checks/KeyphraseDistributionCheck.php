<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class KeyphraseDistributionCheck extends AbstractSeoCheck
{
    public function __construct(
        protected PersianTextNormalizer $persianTextNormalizer,
    )
    {
    }

    public function key(): string
    {
        return 'keyphrase_distribution';
    }

    public function weight(): int
    {
        return 7;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $keyword = trim((string)$input->focusKeyword);
        $content = trim((string)$input->plainTextContent);
        $wordCount = max(0, (int)$input->wordCount);

        if ($keyword === '') {
            return $this->makeResult(
                title: 'توزیع عبارت کلیدی',
                description: 'تا وقتی کلیدواژه کانونی تعیین نشده باشد، توزیع آن قابل بررسی نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 76,
            );
        }

        if ($content === '' || $wordCount < 150) {
            return $this->makeResult(
                title: 'توزیع عبارت کلیدی',
                description: 'متن برای بررسی توزیع عبارت کلیدی هنوز به اندازه کافی طولانی نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'word_count' => $wordCount,
                ],
                priority: 76,
            );
        }

        $normalizedContent = $this->persianTextNormalizer->normalize($content);
        $normalizedKeyword = $this->persianTextNormalizer->normalize($keyword);

        $segments = $this->splitIntoSegments($normalizedContent, 4);

        $coveredSegments = 0;

        foreach ($segments as $segment) {
            if ($this->containsWholePhrase($segment, $normalizedKeyword)) {
                $coveredSegments++;
            }
        }

        $segmentCount = count($segments);
        $coverageRatio = $segmentCount > 0 ? $coveredSegments / $segmentCount : 0;

        if ($coverageRatio >= 0.75) {
            return $this->makeResult(
                title: 'توزیع عبارت کلیدی',
                description: 'عبارت کلیدی به‌خوبی در بخش‌های مختلف متن توزیع شده است. عالی است!',
                status: SeoCheckStatusEnum::GOOD,
                meta: [
                    'segment_count' => $segmentCount,
                    'covered_segments' => $coveredSegments,
                    'coverage_ratio' => round($coverageRatio, 2),
                ],
                priority: 76,
            );
        }

        if ($coverageRatio >= 0.5) {
            return $this->makeResult(
                title: 'توزیع عبارت کلیدی',
                description: 'توزیع عبارت کلیدی بد نیست، اما بهتر است در بخش‌های بیشتری از متن دیده شود.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: [
                    'segment_count' => $segmentCount,
                    'covered_segments' => $coveredSegments,
                    'coverage_ratio' => round($coverageRatio, 2),
                ],
                priority: 76,
            );
        }

        return $this->makeResult(
            title: 'توزیع عبارت کلیدی',
            description: 'توزیع عبارت کلیدی در متن ناهموار است. بهتر است آن را در بخش‌های بیشتری از متن پخش کنید.',
            status: SeoCheckStatusEnum::PROBLEM,
            meta: [
                'segment_count' => $segmentCount,
                'covered_segments' => $coveredSegments,
                'coverage_ratio' => round($coverageRatio, 2),
            ],
            priority: 76,
        );
    }

    /**
     * @return array<int, string>
     */
    protected function splitIntoSegments(string $content, int $segmentCount): array
    {
        $words = preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return [];
        }

        $segmentCount = max(1, $segmentCount);
        $chunkSize = (int)ceil(count($words) / $segmentCount);

        $chunks = array_chunk($words, max(1, $chunkSize));

        return array_map(
            static fn(array $chunk): string => implode(' ', $chunk),
            $chunks,
        );
    }

    protected function containsWholePhrase(string $content, string $keyword): bool
    {
        if ($content === '' || $keyword === '') {
            return false;
        }

        $pattern = '/(?<!\p{L})' . preg_quote($keyword, '/') . '(?!\p{L})/u';

        return preg_match($pattern, $content) === 1;
    }
}
