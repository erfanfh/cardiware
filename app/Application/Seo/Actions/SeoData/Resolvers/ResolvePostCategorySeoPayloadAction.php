<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\TaxonomySeoVariableDataBuilder;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Str;

final class ResolvePostCategorySeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction           $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction      $resolveCanonicalUrlAction,
        protected SeoTemplateParser              $seoTemplateParser,
        protected TaxonomySeoVariableDataBuilder $taxonomySeoVariableDataBuilder,
    )
    {
    }

    public function execute(TaxonomyTerm $category): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->taxonomySeoVariableDataBuilder->build($category);

        $titleTemplate = $category->seoMeta?->title
            ?: get_setting('seo', 'post_category_seo_title')
                ?: $category->title;

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);

        $descriptionTemplate = $category->seoMeta?->description
            ?: get_setting('seo', 'post_category_seo_description')
                ?: $this->resolveFallbackDescription($category);

        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $ogTitleTemplate = $category->seoMeta?->og_title
            ?: get_setting('seo', 'post_category_social_seo_title')
                ?: $title;

        $ogTitle = $this->seoTemplateParser->parse($ogTitleTemplate, $variables);

        $ogDescriptionTemplate = $category->seoMeta?->og_description
            ?: get_setting('seo', 'post_category_social_seo_description')
                ?: $description;

        $ogDescription = $this->seoTemplateParser->parse($ogDescriptionTemplate, $variables);

        $image = $this->resolveFallbackImage($category);

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: $this->resolveCurrentPageUrl($category),
        );

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $defaults['robots'],

            title: $title,
            description: $description,
            headline: $category->title,

            ogTitle: $ogTitle,
            ogDescription: $ogDescription,

            pageImageUrl: $image?->url,
            pageImageWidth: $image?->primaryVariant?->width,
            pageImageHeight: $image?->primaryVariant?->height,
            pageImageCaption: $category->title,

            keywords: [$category->title],
        );
    }

    protected function resolveFallbackDescription(TaxonomyTerm $category): string
    {
        $description = (string)($category->description ?? '');

        if (filled($description)) {
            return Str::limit(trim(strip_tags($description)), 170, '');
        }

        return '';
    }

    protected function resolveFallbackImage(TaxonomyTerm $category): ?MediaFile
    {
        if (!method_exists($category, 'posts')) {
            return null;
        }

        $post = $category->posts()
            ->with(['picture.primaryVariant'])
            ->latest('published_at')
            ->first();

        return $post?->picture;
    }

    protected function resolveCurrentPageUrl(TaxonomyTerm $category): string
    {
        $baseUrl = front_route('posts.categories', ['path' => $category->path]);
        $page = max(1, (int)request()->integer('page', 1));

        if ($page <= 1) {
            return $baseUrl;
        }

        return $baseUrl . '?page=' . $page;
    }
}
