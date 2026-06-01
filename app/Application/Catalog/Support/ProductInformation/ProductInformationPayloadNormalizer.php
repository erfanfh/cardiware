<?php

namespace App\Application\Catalog\Support\ProductInformation;

class ProductInformationPayloadNormalizer
{
    /**
     * Convert value to nullable integer.
     *
     * @param mixed $value
     * @return int|null
     */
    public function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int)$value;
    }

    /**
     * Convert value to integer.
     *
     * @param mixed $value
     * @return int
     */
    public function integer(mixed $value): int
    {
        return (int)($value ?? 0);
    }

    /**
     * Convert value to nullable string.
     *
     * @param mixed $value
     * @return string|null
     */
    public function nullableString(mixed $value): ?string
    {
        $value = trim((string)($value ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * Convert value to boolean.
     *
     * @param mixed $value
     * @return bool
     */
    public function boolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
