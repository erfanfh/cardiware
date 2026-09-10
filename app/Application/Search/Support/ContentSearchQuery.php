<?php

namespace App\Application\Search\Support;

use Illuminate\Database\Eloquent\Builder;

final class ContentSearchQuery
{
    private const SEARCHABLE_COLUMNS = ['title', 'excerpt', 'body'];

    private const MAX_SEARCH_TOKENS = 8;

    private const FULL_TEXT_MIN_TOKEN_LENGTH = 3;

    public function apply(Builder $query, string $searchTerm): Builder
    {
        $searchTerm = $this->normalize($searchTerm);
        $tokens = $this->tokens($searchTerm);

        if ($searchTerm === '' || $tokens === []) {
            return $query->whereRaw('1 = 0');
        }

        $fullTextTokens = array_values(array_filter(
            $tokens,
            fn (string $token): bool => mb_strlen($token) >= self::FULL_TEXT_MIN_TOKEN_LENGTH,
        ));
        $shortTokens = array_values(array_diff($tokens, $fullTextTokens));

        $usesFullText = $query->getConnection()->getDriverName() === 'mysql' && $fullTextTokens !== [];

        if ($usesFullText) {
            $this->applyMySqlFullTextSearch($query, $fullTextTokens, $shortTokens);
        } else {
            $this->applyLikeSearch($query, $tokens);
        }

        $this->orderByRelevance($query, $searchTerm);

        if ($usesFullText) {
            $query->orderByDesc('search_relevance');
        }

        return $query;
    }

    private function applyMySqlFullTextSearch(Builder $query, array $fullTextTokens, array $shortTokens): void
    {
        $columns = $this->wrappedColumns($query);
        $booleanSearch = implode(' ', array_map(
            fn (string $token): string => '+'.$token.'*',
            $fullTextTokens,
        ));

        $query->whereRaw(
            "MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)",
            [$booleanSearch],
        );

        foreach ($shortTokens as $token) {
            $this->whereTokenLike($query, $token);
        }

        $query
            ->select($query->getModel()->qualifyColumn('*'))
            ->selectRaw(
                "MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE) AS search_relevance",
                [$booleanSearch],
            );
    }

    private function applyLikeSearch(Builder $query, array $tokens): void
    {
        foreach ($tokens as $token) {
            $this->whereTokenLike($query, $token);
        }
    }

    private function whereTokenLike(Builder $query, string $token): void
    {
        $pattern = '%'.$this->escapeLike($token).'%';

        $query->where(function (Builder $query) use ($pattern): void {
            foreach (self::SEARCHABLE_COLUMNS as $column) {
                $wrappedColumn = $query->getQuery()->getGrammar()->wrap(
                    $query->getModel()->qualifyColumn($column),
                );

                $query->orWhereRaw("{$wrappedColumn} LIKE ? ESCAPE '!'", [$pattern]);
            }
        });
    }

    private function orderByRelevance(Builder $query, string $searchTerm): Builder
    {
        $title = $query->getQuery()->getGrammar()->wrap(
            $query->getModel()->qualifyColumn('title'),
        );
        $excerpt = $query->getQuery()->getGrammar()->wrap(
            $query->getModel()->qualifyColumn('excerpt'),
        );
        $body = $query->getQuery()->getGrammar()->wrap(
            $query->getModel()->qualifyColumn('body'),
        );

        return $query->orderByRaw(
            "CASE
                WHEN {$title} = ? THEN 0
                WHEN {$title} LIKE ? ESCAPE '!' THEN 1
                WHEN {$title} LIKE ? ESCAPE '!' THEN 2
                WHEN {$excerpt} LIKE ? ESCAPE '!' THEN 3
                WHEN {$body} LIKE ? ESCAPE '!' THEN 4
                ELSE 5
            END",
            [
                $searchTerm,
                $this->escapeLike($searchTerm).'%',
                '%'.$this->escapeLike($searchTerm).'%',
                '%'.$this->escapeLike($searchTerm).'%',
                '%'.$this->escapeLike($searchTerm).'%',
            ],
        );
    }

    private function wrappedColumns(Builder $query): string
    {
        $grammar = $query->getQuery()->getGrammar();

        return implode(', ', array_map(
            fn (string $column): string => $grammar->wrap($query->getModel()->qualifyColumn($column)),
            self::SEARCHABLE_COLUMNS,
        ));
    }

    private function tokens(string $searchTerm): array
    {
        $tokens = preg_split(
            '/[\s\p{P}\p{S}\x{200C}\x{200D}]+/u',
            mb_strtolower($searchTerm),
            -1,
            PREG_SPLIT_NO_EMPTY,
        );

        return array_slice(array_values(array_unique($tokens ?: [])), 0, self::MAX_SEARCH_TOKENS);
    }

    private function normalize(string $searchTerm): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $searchTerm));
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }
}
