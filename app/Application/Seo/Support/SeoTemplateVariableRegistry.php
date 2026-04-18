<?php

namespace App\Application\Seo\Support;

use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

class SeoTemplateVariableRegistry
{
    /**
     * @return array<int, array<string, string>>
     */
    public function forContext(string $context): array
    {
        return collect(SeoTemplateVariableEnum::cases())
            ->filter(fn(SeoTemplateVariableEnum $variable) => $variable->isAvailableFor($context))
            ->map(fn(SeoTemplateVariableEnum $variable) => [
                'key' => $variable->value,
                'token' => $variable->token(),
                'label' => $variable->label(),
                'description' => $variable->description(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string>
     */
    public function allowedTokensForContext(string $context): array
    {
        return collect($this->forContext($context))
            ->pluck('token')
            ->all();
    }
}
