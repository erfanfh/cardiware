<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\ResolveSeoPaginationLinksAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildProductTagSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\Resolvers\ResolveProductTagSeoPayloadAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Domain\Seo\Enums\OgTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ResolveProductTagSeoDataAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $getSeoDefaultsAction,
        private readonly ResolveProductTagSeoPayloadAction $resolveProductTagSeoPayloadAction,
        private readonly BuildProductTagSchemaGraphAction $buildProductTagSchemaGraphAction,
        private readonly RenderSchemaGraphAction $renderSchemaGraphAction,
        private readonly ResolveSeoPaginationLinksAction $resolveSeoPaginationLinksAction,
    ) {}

    public function execute(
        TaxonomyTerm $tag,
        ?LengthAwarePaginator $paginator = null,
        ?string $paginationBaseUrl = null,
    ): SeoDataDTO {
        $defaults = $this->getSeoDefaultsAction->execute();
        $payload = $this->resolveProductTagSeoPayloadAction->execute($tag);

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
                $this->buildProductTagSchemaGraphAction->execute($tag, $payload),
            ),
            pagination: $paginator && $paginationBaseUrl
                ? $this->resolveSeoPaginationLinksAction->execute($paginator, $paginationBaseUrl)
                : null,
        );
    }
}
