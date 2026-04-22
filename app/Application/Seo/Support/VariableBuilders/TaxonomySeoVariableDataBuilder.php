<?php

namespace App\Application\Seo\Support\VariableBuilders;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

class TaxonomySeoVariableDataBuilder
{
    /**
     * @return array<string, string>
     */
    public function build(TaxonomyTerm $term): array
    {
        return [
            SeoTemplateVariableEnum::SITE_TITLE->value => (string)app(GetSeoDefaultsAction::class)->execute()['site_name'],
            SeoTemplateVariableEnum::SEPARATOR->value => (string)get_setting('seo', 'separator', '|'),
            SeoTemplateVariableEnum::TAXONOMY_TITLE->value => (string)$term->title,
        ];
    }
}
