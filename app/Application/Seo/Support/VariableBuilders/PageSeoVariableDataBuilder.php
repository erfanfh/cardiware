<?php

namespace App\Application\Seo\Support\VariableBuilders;


use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Content\Models\Page;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

class PageSeoVariableDataBuilder
{
    /**
     * @return array<string, string>
     */
    public function build(Page $page): array
    {
        return [
            SeoTemplateVariableEnum::SITE_TITLE->value => (string)app(GetSeoDefaultsAction::class)->execute()['site_name'],
            SeoTemplateVariableEnum::SEPARATOR->value => (string)get_setting('seo', 'separator', '|'),
            SeoTemplateVariableEnum::PAGE_TITLE->value => (string)$page->title,
        ];
    }
}
