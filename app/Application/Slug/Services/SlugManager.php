<?php

namespace App\Application\Slug\Services;

use App\Application\Slug\Contracts\SlugScopeChecker;
use App\Application\Slug\DTOs\CheckSlugDTO;
use App\Application\Slug\DTOs\SlugCheckResultDTO;
use RuntimeException;

class SlugManager
{
    /**
     * @param iterable<SlugScopeChecker> $checkers
     */
    public function __construct(
        private readonly iterable                $checkers,
        private readonly SlugNormalizer          $normalizer,
        private readonly SlugSuggestionGenerator $suggestionGenerator,
    )
    {
    }

    /**
     * Check whether a slug is available in the given scope and return a suggestion when needed.
     *
     * @param CheckSlugDTO $dto
     * @return SlugCheckResultDTO
     */
    public function check(CheckSlugDTO $dto): SlugCheckResultDTO
    {
        $normalizedSlug = $this->normalizer->normalize($dto->slug);

        if ($normalizedSlug === '') {
            return new SlugCheckResultDTO(
                available: true,
                slug: $dto->slug,
                normalizedSlug: '',
                suggestedSlug: null,
                message: null,
            );
        }

        $checker = $this->resolveChecker($dto->scope);

        $exists = $checker->exists(
            scope: $dto->scope,
            slug: $normalizedSlug,
            ignoreId: $dto->ignoreId,
            ignoreType: $dto->ignoreType,
        );

        if (!$exists) {
            return new SlugCheckResultDTO(
                available: true,
                slug: $dto->slug,
                normalizedSlug: $normalizedSlug,
                suggestedSlug: null,
                message: null,
            );
        }

        $suggestedSlug = $this->suggestionGenerator->generate(
            checker: $checker,
            scope: $dto->scope,
            baseSlug: $normalizedSlug,
            ignoreId: $dto->ignoreId,
            ignoreType: $dto->ignoreType,
        );

        return new SlugCheckResultDTO(
            available: false,
            slug: $dto->slug,
            normalizedSlug: $normalizedSlug,
            suggestedSlug: $suggestedSlug,
            message: 'این پیوند یکتا قبلاً استفاده شده است.',
        );
    }

    /**
     * Resolve the proper checker for the given scope.
     *
     * @param string $scope
     * @return SlugScopeChecker
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
}
