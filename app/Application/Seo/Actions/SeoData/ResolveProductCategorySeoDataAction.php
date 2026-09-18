<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\ResolveSeoPaginationLinksAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildProductCategorySchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\Resolvers\ResolveProductCategorySeoPayloadAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Domain\Seo\Enums\OgTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ResolveProductCategorySeoDataAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $getSeoDefaultsAction,
        private readonly ResolveProductCategorySeoPayloadAction $resolveProductCategorySeoPayloadAction,
        private readonly BuildProductCategorySchemaGraphAction $buildProductCategorySchemaGraphAction,
        private readonly RenderSchemaGraphAction $renderSchemaGraphAction,
        private readonly ResolveSeoPaginationLinksAction $resolveSeoPaginationLinksAction,
    ) {}

    public function execute(
        TaxonomyTerm $category,
        ?LengthAwarePaginator $paginator = null,
        ?string $paginationBaseUrl = null,
    ): SeoDataDTO {
        $defaults = $this->getSeoDefaultsAction->execute();
        $payload = $this->resolveProductCategorySeoPayloadAction->execute($category);

        return new SeoDataDTO(
            robots: $payload->robots ?: $defaults['robots'],
            title: $payload->title,
            description: $payload->description,
            canonicalUrl: $payload->canonicalUrl,
            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::WEBSITE,
                title: $payload->ogTitle,
                description: $payload->ogDescription,
                url: $payload->canonicalUrl,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $payload->ogImage['url'] ?? null,
                imageWidth: $payload->ogImage['width'] ?? null,
                imageHeight: $payload->ogImage['height'] ?? null,
                imageType: $payload->ogImage['type'] ?? null,
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $payload->xTitle,
                description: $payload->xDescription,
                image: $payload->xImageUrl,
            ),
            schemaJson: $this->renderSchemaGraphAction->execute(
                $this->buildProductCategorySchemaGraphAction->execute($category, $payload),
            ),
            pagination: $paginator && $paginationBaseUrl
                ? $this->resolveSeoPaginationLinksAction->execute($paginator, $paginationBaseUrl)
                : null,
        );
    }
}
