<?php

namespace App\Application\Seo\Checks;

use App\Application\Seo\Contracts\SeoCheckInterface;
use App\Application\Seo\DTOs\SeoCheckResultDTO;
use App\Domain\Seo\Enums\SeoCheckStatusEnum;

abstract class AbstractSeoCheck implements SeoCheckInterface
{
    protected function makeResult(
        string             $title,
        string             $description,
        SeoCheckStatusEnum $status,
        array              $meta = [],
        int                $priority = 50,
    ): SeoCheckResultDTO
    {
        return new SeoCheckResultDTO(
            key: $this->key(),
            title: $title,
            description: $description,
            status: $status,
            score: $this->scoreByStatus($status),
            maxScore: $this->weight(),
            meta: $meta,
            priority: $priority,
        );
    }

    protected function scoreByStatus(SeoCheckStatusEnum $status): int
    {
        return match ($status) {
            SeoCheckStatusEnum::GOOD => $this->weight(),
            SeoCheckStatusEnum::IMPROVEMENT => (int)round($this->weight() * 0.5),
            SeoCheckStatusEnum::PROBLEM => 0,
        };
    }
}
