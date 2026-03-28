<?php

namespace App\Application\Slug\DTOs;

class AssignSlugResultDTO
{
    public function __construct(
        public readonly string  $finalSlug,
        public readonly ?string $previousSlug,
        public readonly bool    $changed,
        public readonly bool    $historyRecorded,
    )
    {
    }

    /**
     * Convert the DTO into an array structure.
     *
     * @return array<string, string|bool|null>
     */
    public function toArray(): array
    {
        return [
            'final_slug' => $this->finalSlug,
            'previous_slug' => $this->previousSlug,
            'changed' => $this->changed,
            'history_recorded' => $this->historyRecorded,
        ];
    }
}
