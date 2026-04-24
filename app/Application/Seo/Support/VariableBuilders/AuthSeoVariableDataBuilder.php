<?php

namespace App\Application\Seo\Support\VariableBuilders;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

final class AuthSeoVariableDataBuilder
{
    /**
     * @return array<string, string>
     */
    public function build(string $pageTitle): array
    {
        $defaults = app(GetSeoDefaultsAction::class)->execute();

        return [
            SeoTemplateVariableEnum::SITE_TITLE->value => (string)$defaults['site_name'],
            SeoTemplateVariableEnum::SEPARATOR->value => (string)get_setting('seo', 'separator', '|'),
            'page_title' => $pageTitle,
        ];
    }
}
