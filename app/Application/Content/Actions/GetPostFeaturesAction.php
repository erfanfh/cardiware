<?php

namespace App\Application\Content\Actions;

use App\Application\Content\DTOs\PostFeaturesDTO;
use App\Domain\Content\Models\Post;
use App\Domain\Feature\Enums\FeatureKeyEnum;
use App\Domain\Feature\Enums\FrontBadgeOptionEnum;
use App\Domain\Feature\Models\FeatureAssignment;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class GetPostFeaturesAction
{
    /**
     * Get the resolved features of the given post.
     *
     * @param Post $post
     * @return PostFeaturesDTO
     */
    public function execute(Post $post): PostFeaturesDTO
    {
        $post->loadMissing([
            'featureAssignments.featureDefinition',
        ]);

        /** @var EloquentCollection<int, FeatureAssignment> $assignments */
        $assignments = $post->featureAssignments;

        $assignmentsByKey = $assignments
            ->filter(fn(FeatureAssignment $assignment) => $assignment->featureDefinition !== null)
            ->keyBy(fn(FeatureAssignment $assignment) => $assignment->featureDefinition->key);

        $frontBadgeValue = $assignmentsByKey->get(FeatureKeyEnum::FRONT_BADGE->value)?->value;

        return new PostFeaturesDTO(
            isSpecial: $assignmentsByKey->has(FeatureKeyEnum::IS_SPECIAL->value),
            isVip: $assignmentsByKey->has(FeatureKeyEnum::IS_VIP->value),
            isUpdated: $assignmentsByKey->has(FeatureKeyEnum::IS_UPDATED->value),
            frontBadge: $frontBadgeValue !== null
                ? FrontBadgeOptionEnum::tryFrom((string)$frontBadgeValue)
                : null,
        );
    }
}
