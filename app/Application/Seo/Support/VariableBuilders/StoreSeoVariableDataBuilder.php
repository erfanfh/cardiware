<?php

namespace App\Application\Seo\Support\VariableBuilders;


use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

class StoreSeoVariableDataBuilder
{
    /**
     * @return array<string, string>
     */
    public function build(Store $store): array
    {
        return [
            SeoTemplateVariableEnum::SITE_TITLE->value => (string)app(GetSeoDefaultsAction::class)->execute()['site_name'],
            SeoTemplateVariableEnum::SEPARATOR->value => (string)get_setting('seo', 'separator', '|'),
            SeoTemplateVariableEnum::STORE_TITLE->value => (string)$store->title,
        ];
    }
}
