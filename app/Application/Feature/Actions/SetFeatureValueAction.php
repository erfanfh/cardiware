<?php

namespace App\Application\Feature\Actions;

use App\Domain\Feature\Enums\FeatureKeyEnum;
use App\Domain\Feature\Enums\ValueTypeEnum;
use App\Domain\Feature\Models\FeatureAssignment;
use App\Domain\Feature\Models\FeatureDefinition;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class SetFeatureValueAction
{
    public function __construct(
        protected GetFeatureDefinitionByKeyAction $getFeatureDefinitionByKeyAction,
    )
    {
    }

    /**
     * @param Model $subject
     * @param FeatureKeyEnum $key
     * @param string|int|bool|null $value
     * @return void
     */
    public function execute(Model $subject, FeatureKeyEnum $key, string|int|bool|null $value = null): void
    {
        $definition = $this->getFeatureDefinitionByKeyAction->execute($key);

        $normalizedValue = $this->normalizeValue($definition, $value);

        $attributes = [
            'feature_definition_id' => $definition->id,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
        ];

        if ($definition->value_type === ValueTypeEnum::FLAG) {
            if ($normalizedValue === null) {
                FeatureAssignment::query()->where($attributes)->delete();
                return;
            }

            FeatureAssignment::query()->updateOrCreate(
                $attributes,
                []
            );

            return;
        }

        if ($normalizedValue === null || $normalizedValue === '') {
            FeatureAssignment::query()->where($attributes)->delete();
            return;
        }

        FeatureAssignment::query()->updateOrCreate(
            $attributes,
            ['value' => (string)$normalizedValue]
        );
    }

    /**
     * @param FeatureDefinition $definition
     * @param string|int|bool|null $value
     * @return string|int|bool|null
     */
    protected function normalizeValue(FeatureDefinition $definition, string|int|bool|null $value): string|int|bool|null
    {
        return match ($definition->value_type) {
            ValueTypeEnum::FLAG => $this->normalizeFlagValue($value),
            ValueTypeEnum::BOOL => $this->normalizeBoolValue($value),
            ValueTypeEnum::INT => $this->normalizeIntValue($value),
            ValueTypeEnum::STRING => $this->normalizeStringValue($definition, $value),
        };
    }

    /**
     * @param string|int|bool|null $value
     * @return string|null
     */
    protected function normalizeFlagValue(string|int|bool|null $value): ?string
    {
        if ($value === null || $value === false || $value === 0 || $value === '0' || $value === '') {
            return null;
        }

        return '1';
    }

    /**
     * @param string|int|bool|null $value
     * @return string|null
     */
    protected function normalizeBoolValue(string|int|bool|null $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL) ? '1' : '0';
    }

    /**
     * @param string|int|bool|null $value
     * @return int|null
     */
    protected function normalizeIntValue(string|int|bool|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            throw new InvalidArgumentException('The given feature value must be numeric.');
        }

        return (int)$value;
    }

    /**
     * @param FeatureDefinition $definition
     * @param string|int|bool|null $value
     * @return string|null
     */
    protected function normalizeStringValue(FeatureDefinition $definition, string|int|bool|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalizedValue = trim((string)$value);

        if ($normalizedValue === '') {
            return null;
        }

        $allowedValues = $definition->allowed_values ?? [];

        if ($allowedValues !== [] && !in_array($normalizedValue, $allowedValues, true)) {
            throw new InvalidArgumentException(sprintf(
                'The given value [%s] is not allowed for feature [%s].',
                $normalizedValue,
                $definition->key
            ));
        }

        return $normalizedValue;
    }
}
