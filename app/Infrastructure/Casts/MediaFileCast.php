<?php

namespace App\Infrastructure\Casts;

use App\Application\Media\DTOs\MediaFileDTO;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JsonException;

class MediaFileCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?MediaFileDTO
    {
        if ($value === null)
            return null;

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            if (json_last_error() !== JSON_ERROR_NONE)
                throw new JsonException("Error decoding JSON for key '$key': " . json_last_error_msg());

            return new MediaFileDTO($decoded['id'] ?? null, $decoded['path'] ?? null);
        } catch (JsonException $e) {
            throw new InvalidArgumentException("Value for '$key' is not a valid JSON array: " . $e->getMessage());
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null)
            return null;

        $value = [
            'id' => $value['id'] ?? null,
            'path' => $value['path'] ?? null
        ];

        try {
            $json = json_encode($value, JSON_THROW_ON_ERROR);
            if (json_last_error() !== JSON_ERROR_NONE)
                throw new JsonException("Error encoding JSON for key '$key': " . json_last_error_msg());

            return $json;
        } catch (JsonException $e) {
            throw new InvalidArgumentException("Could not encode array to JSON for key '$key': " . $e->getMessage());
        }
    }
}
