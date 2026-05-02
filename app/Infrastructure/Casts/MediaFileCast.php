<?php

namespace App\Infrastructure\Casts;

use App\Application\Media\DTOs\MediaFileDTO;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JsonException;

class MediaFileCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): MediaFileDTO
    {
        if ($value === null) {
            return new MediaFileDTO();
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return new MediaFileDTO(
                $decoded['id'] ?? null,
                $decoded['path'] ?? null
            );
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                "Value for '$key' is not a valid JSON array: " . $e->getMessage()
            );
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $id = $value['id'] ?? null;
        $path = $this->normalizePath($value['path'] ?? null);

        $value = [
            'id' => $id,
            'path' => $path,
        ];

        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                "Could not encode array to JSON for key '$key': " . $e->getMessage()
            );
        }
    }

    private function normalizePath(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsedPath = parse_url($path, PHP_URL_PATH);

            return $this->normalizeRelativePath($parsedPath);
        }

        return $this->normalizeRelativePath($path);
    }

    private function normalizeRelativePath(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        return '/' . ltrim($path, '/');
    }
}
