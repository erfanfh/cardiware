<?php

namespace App\Application\Content\DTOs;

use App\Domain\Feature\Enums\FrontBadgeOptionEnum;

class PostFeaturesDTO
{
    public function __construct(
        public readonly bool                  $isSpecial,
        public readonly bool                  $isVip,
        public readonly bool                  $isUpdated,
        public readonly ?FrontBadgeOptionEnum $frontBadge,
    )
    {
    }

    /**
     * Determine whether the post has a front badge.
     *
     * @return bool
     */
    public function hasFrontBadge(): bool
    {
        return $this->frontBadge !== null;
    }

    /**
     * Get the front badge raw value.
     *
     * @return string|null
     */
    public function frontBadgeValue(): ?string
    {
        return $this->frontBadge?->value;
    }

    /**
     * Get the front badge display label.
     *
     * @return string|null
     */
    public function frontBadgeLabel(): ?string
    {
        return $this->frontBadge?->label();
    }

    /**
     * Convert the DTO to a simple array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'is_special' => $this->isSpecial,
            'is_vip' => $this->isVip,
            'is_updated' => $this->isUpdated,
            'front_badge' => $this->frontBadge?->value,
            'front_badge_label' => $this->frontBadge?->label(),
        ];
    }
}
