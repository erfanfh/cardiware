<?php

namespace App\Application\Seo\Support;

class SeoTemplateParser
{
    /**
     * @param array<string, scalar|null> $data
     */
    public function parse(string $template, array $data): string
    {
        return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function (array $matches) use ($data) {
            $key = $matches[1];

            return array_key_exists($key, $data)
                ? (string)($data[$key] ?? '')
                : '';
        }, $template) ?? $template;
    }

    /**
     * @return array<string>
     */
    public function extractVariables(string $template): array
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $template, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public function hasUnknownVariables(string $template, string $context, SeoTemplateVariableRegistry $registry): bool
    {
        $usedVariables = $this->extractVariables($template);

        $allowed = collect($registry->forContext($context))
            ->pluck('key')
            ->all();

        return collect($usedVariables)->contains(
            fn(string $variable) => !in_array($variable, $allowed, true)
        );
    }
}
