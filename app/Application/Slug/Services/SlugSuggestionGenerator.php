<?php

namespace App\Application\Slug\Services;

use App\Application\Slug\Contracts\SlugScopeChecker;

class SlugSuggestionGenerator
{
    /**
     * Generate the next available slug suggestion for the given scope.
     *
     * @param SlugScopeChecker $checker
     * @param string $scope
     * @param string $baseSlug
     * @param int|null $ignoreId
     * @param string|null $ignoreType
     * @param int $maxAttempts
     * @return string
     */
    public function generate(
        SlugScopeChecker $checker,
        string           $scope,
        string           $baseSlug,
        ?int             $ignoreId = null,
        ?string          $ignoreType = null,
        int              $maxAttempts = 999,
    ): string
    {
        $candidate = $baseSlug;
        $counter = 2;

        while ($checker->exists($scope, $candidate, $ignoreId, $ignoreType)) {
            $candidate = "{$baseSlug}-{$counter}";
            $counter++;

            if ($counter > $maxAttempts) {
                break;
            }
        }

        return $candidate;
    }
}
