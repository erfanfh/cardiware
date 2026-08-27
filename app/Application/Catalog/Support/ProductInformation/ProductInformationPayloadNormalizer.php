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
        // A browser <textarea>'s captured value is not guaranteed to use a bare '\n' for
        // every line break -- Shift+Enter has been observed producing a lone '\r' in some
        // environments. A bare '\r' with no '\n' renders as nothing at all (no visible
        // break) both back in this same textarea on reload and inside the launcher's own
        // text renderer, which is what actually read as "every line glued together" for
        // multi-line fields like launcher_usage_guide_content. Normalizing every line
        // ending to '\n' here, once, at the point of saving, fixes it for every reader.
        $value = str_replace(["\r\n", "\r"], "\n", (string)($value ?? ''));
        $value = trim($value);

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
