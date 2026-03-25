<?php

namespace App\Application\Slug\Services;

use App\Application\Slug\Contracts\SlugPersistenceDriver;
use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Slug\DTOs\AssignSlugResultDTO;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

class SlugAssignmentManager
{
    /**
     * @param iterable<SlugScopeChecker> $checkers
     * @param iterable<SlugPersistenceDriver> $drivers
     */
    public function __construct(
        private readonly iterable                $checkers,
        private readonly iterable                $drivers,
        private readonly SlugNormalizer          $normalizer,
        private readonly SlugSuggestionGenerator $suggestionGenerator,
        private readonly ConnectionInterface     $db,
    )
    {
    }

    /**
     * Assign a final slug to the given model and persist related history when required.
     */
    public function assign(AssignSlugDTO $dto): AssignSlugResultDTO
    {
        $normalizedSlug = $this->normalizer->normalize($dto->desiredSlug);
        $driver = $this->resolveDriver($dto->scope);
        $checker = $this->resolveChecker($dto->scope);

        $currentSlug = $driver->getCurrentSlug($dto->model);

        if ($normalizedSlug === '') {
            $normalizedSlug = $currentSlug ?? '';
        }

        if ($normalizedSlug === '' || $normalizedSlug === $currentSlug) {
            return new AssignSlugResultDTO(
                finalSlug: $currentSlug ?? '',
                previousSlug: $currentSlug,
                changed: false,
                historyRecorded: false,
            );
        }

        $ignoreId = $dto->model->getKey();
        $ignoreType = $dto->model::class;

        $finalSlug = $normalizedSlug;

        if ($checker->exists($dto->scope, $normalizedSlug, $ignoreId, $ignoreType)) {
            $finalSlug = $this->suggestionGenerator->generate(
                checker: $checker,
                scope: $dto->scope,
                baseSlug: $normalizedSlug,
                ignoreId: is_numeric($ignoreId) ? (int)$ignoreId : null,
                ignoreType: $ignoreType,
            );
        }

        /** @var array{
         *     final_slug: string,
         *     previous_slug: ?string,
         *     changed: bool,
         *     history_recorded: bool
         * } $result
         */
        $result = $this->db->transaction(
            fn(): array => $driver->persist($dto->model, $dto->scope, $finalSlug)
        );

        return new AssignSlugResultDTO(
            finalSlug: $result['final_slug'],
            previousSlug: $result['previous_slug'],
            changed: $result['changed'],
            historyRecorded: $result['history_recorded'],
        );
    }

    /**
     * Resolve the checker responsible for the given scope.
     */
    private function resolveChecker(string $scope): SlugScopeChecker
    {
        foreach ($this->checkers as $checker) {
            if ($checker->supports($scope)) {
                return $checker;
            }
        }

        throw new RuntimeException("No slug checker found for scope [{$scope}].");
    }

    /**
     * Resolve the persistence driver responsible for the given scope.
     */
    private function resolveDriver(string $scope): SlugPersistenceDriver
    {
        foreach ($this->drivers as $driver) {
            if ($driver->supports($scope)) {
                return $driver;
            }
        }

        throw new RuntimeException("No slug checker found for scope [{$scope}].");
    }
}
