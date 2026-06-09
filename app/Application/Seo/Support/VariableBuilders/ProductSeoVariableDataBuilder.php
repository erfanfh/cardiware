<?php

namespace App\Application\Seo\Support\VariableBuilders;


use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

class ProductSeoVariableDataBuilder
{
    /**
     * @return array<string, string>
     */
    public function build(Product $product): array
    {
        return [
            SeoTemplateVariableEnum::SITE_TITLE->value => (string)app(GetSeoDefaultsAction::class)->execute()['site_name'],
            SeoTemplateVariableEnum::SEPARATOR->value => (string)get_setting('seo', 'separator', '|'),
            SeoTemplateVariableEnum::PRODUCT_TITLE->value => (string)$product->title,
        ];
    }
}
