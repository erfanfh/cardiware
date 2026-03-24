<?php

namespace App\Infrastructure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonException;

class RepeaterArrayCast implements CastsAttributes
{
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return array
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = $this->decodeToArray($key, $value);

        return $this->normalizeRepeaterList($decoded);
    }

    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return string|null
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $array = $this->toArray($key, $value);
        $normalized = $this->normalizeRepeaterList($array);

        try {
            return json_encode($normalized, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                "Could not encode repeater value for key '{$key}' to JSON: {$e->getMessage()}",
                previous: $e,
            );
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return array
     */
    protected function decodeToArray(string $key, mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Collection) {
            return $value->all();
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException("Value for '{$key}' must be a JSON string or array.");
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                "Value for '{$key}' is not valid JSON: {$e->getMessage()}",
                previous: $e,
            );
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return array
     */
    protected function toArray(string $key, mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Collection) {
            return $value->all();
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        throw new InvalidArgumentException("Value for '{$key}' must be an array.");
    }

    /**
     * @param array $items
     * @return array
     */
    protected function normalizeRepeaterList(array $items): array
    {
        $items = $this->sortRepeaterItems($items);

        $normalized = [];

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $item = $this->normalizeAssocArray($item);
            $item['sort_order'] = $index;

            $normalized[] = $item;
        }

        return $normalized;
    }

    /**
     * @param array $item
     * @return array
     */
    protected function normalizeAssocArray(array $item): array
    {
        unset($item['__key'], $item['_open']);

        foreach ($item as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if ($this->isRepeaterLikeList($value)) {
                $item[$key] = $this->normalizeRepeaterList($value);
                continue;
            }

            $item[$key] = $this->normalizeAssocArray($value);
        }

        return $item;
    }

    /**
     * @param array $items
     * @return array
     */
    protected function sortRepeaterItems(array $items): array
    {
        $items = array_values($items);

        usort($items, function (mixed $a, mixed $b): int {
            $aOrder = is_array($a) ? $this->extractSortOrder($a) : PHP_INT_MAX;
            $bOrder = is_array($b) ? $this->extractSortOrder($b) : PHP_INT_MAX;

            return $aOrder <=> $bOrder;
        });

        return array_values($items);
    }

    /**
     * @param array $item
     * @return int
     */
    protected function extractSortOrder(array $item): int
    {
        $value = $item['sort_order'] ?? PHP_INT_MAX;

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        return PHP_INT_MAX;
    }

    /**
     * @param array $value
     * @return bool
     */
    protected function isRepeaterLikeList(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        foreach (array_keys($value) as $key) {
            if (!is_int($key) && !ctype_digit((string)$key)) {
                return false;
            }
        }

        foreach ($value as $item) {
            if (!is_array($item)) {
                return false;
            }
        }

        return true;
    }
}
