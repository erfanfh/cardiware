<?php

namespace App\Infrastructure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JsonException;

class ArrayCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null)
            return [];

        if (is_array($value))
            return $value;

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : [];
        } catch (JsonException $e) {
            throw new InvalidArgumentException("Value for '$key' is not a valid JSON array: " . $e->getMessage(), previous: $e);
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null)
            return null;

        if (!is_array($value))
            throw new InvalidArgumentException("Value for '$key' must be an array.");

        try {
            $json = json_encode($value, JSON_THROW_ON_ERROR);

            return $json;
        } catch (JsonException $e) {
            throw new InvalidArgumentException("Could not encode array to JSON for key '$key': " . $e->getMessage(), previous: $e);
        }
    }
}
