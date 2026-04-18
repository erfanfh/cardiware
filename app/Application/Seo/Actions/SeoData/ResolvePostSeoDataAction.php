<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\PostSeoVariableDataBuilder;
use App\Domain\Content\Models\Post;

class ResolvePostSeoDataAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $getSeoDefaultsAction,
    )
    {
    }

    public function execute(Post $post): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $data = app(PostSeoVariableDataBuilder::class)->build($post);

        $titleTemplate = $post->seoMeta?->title ?: get_setting('seo', 'post_seo_title');
        $title = app(SeoTemplateParser::class)->parse($titleTemplate, $data);

        $descriptionTemplate = $post->seoMeta?->description ?: get_setting('seo', 'post_seo_description');
        $description = app(SeoTemplateParser::class)->parse($descriptionTemplate, $data);

        return new SeoDataDTO(
            title: $title,
            description: $description,
            openGraph: new OpenGraphDataDTO(
                locale: $defaults['og_locale'],
            )
        );
    }
}
