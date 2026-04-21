<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class SlugLengthCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'slug_length';
    }

    public function weight(): int
    {
        return 5;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $slug = trim((string)$input->slug);

        if ($slug === '') {
            return $this->makeResult(
                title: 'طول نامک',
                description: 'تا وقتی نامک مشخص نباشد، طول آن قابل بررسی نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 61,
            );
        }

        $decodedSlug = urldecode($slug);
        $length = mb_strlen($decodedSlug);

        if ($length < 8) {
            return $this->makeResult(
                title: 'طول نامک',
                description: "نامک {$length} کاراکتر دارد و کمی کوتاه به نظر می‌رسد. بهتر است واضح‌تر و توصیفی‌تر باشد.",
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: ['length' => $length],
                priority: 61,
            );
        }

        if ($length <= 75) {
            return $this->makeResult(
                title: 'طول نامک',
                description: "طول نامک {$length} کاراکتر است. خیلی خوب است.",
                status: SeoCheckStatusEnum::GOOD,
                meta: ['length' => $length],
                priority: 61,
            );
        }

        return $this->makeResult(
            title: 'طول نامک',
            description: "نامک {$length} کاراکتر دارد و بیش از حد طولانی به نظر می‌رسد. بهتر است کوتاه‌تر و متمرکزتر شود.",
            status: SeoCheckStatusEnum::PROBLEM,
            meta: ['length' => $length],
            priority: 61,
        );
    }
}
