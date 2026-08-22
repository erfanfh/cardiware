<?php

namespace App\Infrastructure\Slug\Drivers;

use App\Application\Slug\Contracts\SlugPersistenceDriver;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Database\Eloquent\Model;

class RaffleSlugPersistenceDriver implements SlugPersistenceDriver
{
    public function supports(SlugScopeEnum $scope): bool
    {
        return $scope === SlugScopeEnum::RAFFLE;
    }

    public function getCurrentSlug(Model $model): ?string
    {
        $slug = $model->getAttribute('slug');

        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    /**
     * Persist only the current campaign slug. Raffles intentionally do not
     * retain slug history or redirect previous URLs.
     *
     * @return array{
     *     final_slug: string,
     *     previous_slug: ?string,
     *     changed: bool,
     *     history_recorded: bool
     * }
     */
    public function persist(Model $model, SlugScopeEnum $scope, string $finalSlug): array
    {
        $previousSlug = $this->getCurrentSlug($model);

        if ($previousSlug === $finalSlug) {
            return [
                'final_slug' => $finalSlug,
                'previous_slug' => $previousSlug,
                'changed' => false,
                'history_recorded' => false,
            ];
        }

        $model->forceFill(['slug' => $finalSlug])->save();

        return [
            'final_slug' => $finalSlug,
            'previous_slug' => $previousSlug,
            'changed' => true,
            'history_recorded' => false,
        ];
    }
}
