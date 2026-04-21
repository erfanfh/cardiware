<?php

namespace App\Application\Seo\DTOs;

use App\Domain\Seo\Enums\SeoCheckStatusEnum;

final readonly class SeoCheckResultDTO
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string             $key,
        public string             $title,
        public string             $description,
        public SeoCheckStatusEnum $status,
        public int                $score,
        public int                $maxScore,
        public array              $meta = [],
        public int                $priority = 50,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'score' => $this->score,
            'max_score' => $this->maxScore,
            'meta' => $this->meta,
            'priority' => $this->priority,
        ];
    }
}
