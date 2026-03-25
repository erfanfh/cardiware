<?php

namespace App\Application\Slug\Services;

use App\Application\Slug\Contracts\SlugResolverDriver;
use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use RuntimeException;

class SlugResolverManager
{
    /**
     * @param iterable<SlugResolverDriver> $drivers
     */
    public function __construct(
        private readonly iterable $drivers,
    )
    {
    }

    /**
     * Resolve the given slug value using the proper resolver driver.
     */
    public function resolve(ResolveSlugDTO $dto): ResolvedSlugDTO
    {
        $driver = $this->resolveDriver($dto->scope);

        return $driver->resolve($dto->scope, $dto->value);
    }

    /**
     * Resolve the driver responsible for the given scope.
     */
    private function resolveDriver(string $scope): SlugResolverDriver
    {
        foreach ($this->drivers as $driver) {
            if ($driver->supports($scope)) {
                return $driver;
            }
        }

        throw new RuntimeException("No slug checker found for scope [{$scope}].");
    }
}
