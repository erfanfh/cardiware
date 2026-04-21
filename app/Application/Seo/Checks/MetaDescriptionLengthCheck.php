<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final class MetaDescriptionLengthCheck extends AbstractSeoCheck
{
    public function key(): string
    {
        return 'meta_description_length';
    }

    public function weight(): int
    {
        return 7;
    }

    public function analyze(SeoAnalysisInputDTO $input): SeoCheckResultDTO
    {
        $description = trim((string)$input->resolvedSeoDescription);

        if ($description === '') {
            return $this->makeResult(
                title: 'تعداد کاراکترهای توضیحات متا',
                description: 'تا وقتی توضیح متا در دسترس نباشد، طول آن قابل بررسی نیست.',
                status: SeoCheckStatusEnum::IMPROVEMENT,
                priority: 84,
            );
        }

        $length = mb_strlen($description);

        if ($length < 70) {
            return $this->makeResult(
                title: 'تعداد کاراکترهای توضیحات متا',
                description: "توضیحات متا {$length} کاراکتر دارد و کمی کوتاه به نظر می‌رسد. بهتر است کامل‌تر و توصیفی‌تر باشد.",
                status: SeoCheckStatusEnum::IMPROVEMENT,
                meta: ['length' => $length],
                priority: 84,
            );
        }

        if ($length <= 160) {
            return $this->makeResult(
                title: 'تعداد کاراکترهای توضیحات متا',
                description: "توضیحات متا {$length} کاراکتر دارد. آفرین!",
                status: SeoCheckStatusEnum::GOOD,
                meta: ['length' => $length],
                priority: 84,
            );
        }

        return $this->makeResult(
            title: 'تعداد کاراکترهای توضیحات متا',
            description: "توضیحات متا {$length} کاراکتر دارد و احتمالاً برای نتایج جستجو بیش از حد بلند است. بهتر است کوتاه‌تر شود.",
            status: SeoCheckStatusEnum::PROBLEM,
            meta: ['length' => $length],
            priority: 84,
        );
    }
}
