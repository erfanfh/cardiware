<?php

namespace App\Application\Content\Actions;

use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Career\Models\CareerPosition;
use App\Infrastructure\Services\Setting\Groups\CareersSettings;

class GetCareerPageDataAction
{
    public function __construct(
        private readonly CareersSettings $settings,
    ) {}

    /** @return array<string, mixed> */
    public function execute(?int $requestedPositionId = null): array
    {
        $positions = app(PublicReadCache::class)->remember(
            'career-positions', [], [CareerPosition::class],
            fn () => CareerPosition::publiclyAvailable()->latest('published_at')->latest('id')->get(),
            [CareerPosition::class => ['published_at']],
        );

        return [
            'careers' => $this->settings->values(),
            'positions' => $positions,
            'teamCount' => $positions->pluck('department')->filter()->unique()->count(),
            'selectedPositionId' => $positions->contains('id', $requestedPositionId) ? $requestedPositionId : null,
        ];
    }
}
