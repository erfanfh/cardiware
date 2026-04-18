<?php

namespace App\Application\Seo\Support\VariableBuilders;


use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Content\Models\Post;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

class PostSeoVariableDataBuilder
{
    /**
     * @return array<string, string>
     */
    public function build(Post $post): array
    {
        return [
            SeoTemplateVariableEnum::SITE_TITLE->value => (string)app(GetSeoDefaultsAction::class)->execute()['site_name'],
            SeoTemplateVariableEnum::SEPARATOR->value => (string)get_setting('seo', 'separator', '|'),
            SeoTemplateVariableEnum::POST_TITLE->value => (string)$post->title,
        ];
    }
}
