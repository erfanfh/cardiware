<?php

namespace App\Infrastructure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class FloatCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        if ($value === null)
            return null;

        if (!is_numeric($value))
            throw new InvalidArgumentException("Value for '$key' is not a valid float.");

        return (float)$value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null)
            return null;

        if (!is_float($value) && !is_numeric($value))
            throw new InvalidArgumentException("Value for '$key' must be a float.");

        return (string)(float)$value;
    }
}
