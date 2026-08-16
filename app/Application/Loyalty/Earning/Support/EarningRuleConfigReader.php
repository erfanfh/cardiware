<?php

namespace App\Application\Loyalty\Earning\Support;

use App\Application\Loyalty\Earning\Exceptions\InvalidEarningRuleConfigurationException;
use App\Domain\Loyalty\Models\EarningRule;

class EarningRuleConfigReader
{
    public function positiveInt(EarningRule $rule, string $key, ?int $default = null): int
    {
        $config = $rule->config ?? [];

        if (!array_key_exists($key, $config) && $default !== null) {
            if ($default <= 0) {
                throw InvalidEarningRuleConfigurationException::forKey(
                    $rule,
                    $key,
                    'default value must be greater than zero.',
                );
            }

            return $default;
        }

        $value = $this->intValue($rule, $key);

        if ($value <= 0) {
            throw InvalidEarningRuleConfigurationException::forKey(
                $rule,
                $key,
                'value must be greater than zero.',
            );
        }

        return $value;
    }

    public function nonNegativeInt(EarningRule $rule, string $key, int $default = 0): int
    {
        if (!array_key_exists($key, $rule->config ?? [])) {
            return $default;
        }

        $value = $this->intValue($rule, $key);

        if ($value < 0) {
            throw InvalidEarningRuleConfigurationException::forKey(
                $rule,
                $key,
                'value cannot be negative.',
            );
        }

        return $value;
    }

    public function nullablePositiveInt(EarningRule $rule, string $key): ?int
    {
        $config = $rule->config ?? [];

        if (!array_key_exists($key, $config) || $config[$key] === null || $config[$key] === '') {
            return null;
        }

        return $this->positiveInt($rule, $key);
    }

    public function bool(EarningRule $rule, string $key, bool $default): bool
    {
        $config = $rule->config ?? [];

        if (!array_key_exists($key, $config)) {
            return $default;
        }

        $value = $config[$key];

        if (is_bool($value)) {
            return $value;
        }

        if ($value === 1 || $value === 0 || $value === '1' || $value === '0') {
            return (bool)$value;
        }

        throw InvalidEarningRuleConfigurationException::forKey(
            $rule,
            $key,
            'value must be boolean.',
        );
    }

    private function intValue(EarningRule $rule, string $key): int
    {
        $config = $rule->config ?? [];

        if (!array_key_exists($key, $config)) {
            throw InvalidEarningRuleConfigurationException::forKey(
                $rule,
                $key,
                'required value is missing.',
            );
        }

        $value = $config[$key];

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int)$value;
        }

        throw InvalidEarningRuleConfigurationException::forKey(
            $rule,
            $key,
            'value must be an integer.',
        );
    }
}
