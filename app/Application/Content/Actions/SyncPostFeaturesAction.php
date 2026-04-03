<?php

namespace App\Application\Content\Actions;

use App\Application\Feature\Actions\SetFeatureValueAction;
use App\Domain\Content\Models\Post;
use App\Domain\Feature\Enums\FeatureKeyEnum;
use App\Domain\Feature\Enums\FrontBadgeOptionEnum;
use InvalidArgumentException;

class SyncPostFeaturesAction
{
    public function __construct(
        protected SetFeatureValueAction $setFeatureValueAction,
    )
    {
    }

    /**
     * Sync the supported feature values for the given post.
     *
     * Supported input keys:
     * - is_special
     * - is_vip
     * - is_updated
     * - front_badge
     *
     * @param Post $post
     * @param array<string, mixed> $data
     * @return void
     */
    public function execute(Post $post, array $data): void
    {
        $this->setFeatureValueAction->execute(
            $post,
            FeatureKeyEnum::IS_SPECIAL,
            $this->resolveBooleanInput($data, 'is_special')
        );

        $this->setFeatureValueAction->execute(
            $post,
            FeatureKeyEnum::IS_VIP,
            $this->resolveBooleanInput($data, 'is_vip')
        );

        $this->setFeatureValueAction->execute(
            $post,
            FeatureKeyEnum::IS_UPDATED,
            $this->resolveBooleanInput($data, 'is_updated')
        );

        $this->setFeatureValueAction->execute(
            $post,
            FeatureKeyEnum::FRONT_BADGE,
            $this->resolveFrontBadgeValue($data)
        );
    }

    /**
     * Resolve a checkbox-like boolean input from the given payload.
     *
     * Missing keys are treated as false so that update forms can clear flags
     * when the checkbox is not checked.
     *
     * @param array<string, mixed> $data
     * @param string $key
     * @return bool
     */
    protected function resolveBooleanInput(array $data, string $key): bool
    {
        return filter_var($data[$key] ?? false, FILTER_VALIDATE_BOOL);
    }

    /**
     * Resolve and validate the front badge value.
     *
     * An empty or missing value removes the assignment.
     *
     * @param array<string, mixed> $data
     * @return string|null
     */
    protected function resolveFrontBadgeValue(array $data): ?string
    {
        $value = trim((string)($data['front_badge'] ?? ''));

        if ($value === '') {
            return null;
        }

        $badge = FrontBadgeOptionEnum::tryFrom($value);

        if ($badge === null) {
            throw new InvalidArgumentException(sprintf(
                'The given front badge value [%s] is invalid.',
                $value
            ));
        }

        return $badge->value;
    }
}
