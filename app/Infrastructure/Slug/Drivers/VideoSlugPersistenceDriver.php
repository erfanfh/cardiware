<?php

namespace App\Infrastructure\Slug\Drivers;

use App\Application\Slug\Contracts\SlugPersistenceDriver;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Video\Models\VideoSlug;
use Illuminate\Database\Eloquent\Model;

class VideoSlugPersistenceDriver implements SlugPersistenceDriver
{
    public function supports(SlugScopeEnum $scope): bool { return $scope === SlugScopeEnum::VIDEO; }

    public function getCurrentSlug(Model $model): ?string
    {
        $slug = $model->getAttribute('slug');
        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    public function persist(Model $model, SlugScopeEnum $scope, string $finalSlug): array
    {
        $previousSlug = $this->getCurrentSlug($model);
        if ($previousSlug === $finalSlug) {
            return ['final_slug' => $finalSlug, 'previous_slug' => $previousSlug, 'changed' => false, 'history_recorded' => false];
        }

        $model->forceFill(['slug' => $finalSlug])->save();
        VideoSlug::query()->where('video_id', $model->getKey())->where('is_current', true)->update(['is_current' => false]);
        VideoSlug::query()->updateOrCreate(
            ['route_key' => $finalSlug],
            ['video_id' => $model->getKey(), 'is_current' => true],
        );

        return ['final_slug' => $finalSlug, 'previous_slug' => $previousSlug, 'changed' => true, 'history_recorded' => true];
    }
}
