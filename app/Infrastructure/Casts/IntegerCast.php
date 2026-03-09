<?php

namespace App\Infrastructure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class IntegerCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null)
            return null;

        if (!is_numeric($value))
            throw new InvalidArgumentException("Value for '$key' is not a valid integer.");

        return (int)$value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null)
            return null;

        if (!is_int($value) && !is_numeric($value)) {
            throw new InvalidArgumentException("Value for '$key' must be an integer.");
        }

        return (string)(int)$value;
    }
}
