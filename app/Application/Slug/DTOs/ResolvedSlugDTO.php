<?php

namespace App\Application\Slug\DTOs;

use Illuminate\Database\Eloquent\Model;

class ResolvedSlugDTO
{
    public function __construct(
        public readonly bool    $found,
        public readonly bool    $isCurrent,
        public readonly bool    $shouldRedirect,
        public readonly string  $requestedValue,
        public readonly ?string $currentValue = null,
        public readonly ?string $redirectTo = null,
        public readonly ?Model  $model = null,
    )
    {
    }

    /**
     * Create a DTO instance for a missing slug.
     */
    public static function notFound(string $requestedValue): self
    {
        return new self(
            found: false,
            isCurrent: false,
            shouldRedirect: false,
            requestedValue: $requestedValue,
            currentValue: null,
            redirectTo: null,
            model: null,
        );
    }

    /**
     * Create a DTO instance for a resolved current slug.
     */
    public static function current(string $requestedValue, string $currentValue, Model $model): self
    {
        return new self(
            found: true,
            isCurrent: true,
            shouldRedirect: false,
            requestedValue: $requestedValue,
            currentValue: $currentValue,
            redirectTo: null,
            model: $model,
        );
    }

    /**
     * Create a DTO instance for a historical slug that should redirect.
     */
    public static function redirect(
        string $requestedValue,
        string $currentValue,
        string $redirectTo,
        Model  $model,
    ): self
    {
        return new self(
            found: true,
            isCurrent: false,
            shouldRedirect: true,
            requestedValue: $requestedValue,
            currentValue: $currentValue,
            redirectTo: $redirectTo,
            model: $model,
        );
    }
}
