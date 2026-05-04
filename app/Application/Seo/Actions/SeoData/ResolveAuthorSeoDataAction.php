<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\ResolveSeoPaginationLinksAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildAuthorSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\Resolvers\ResolveAuthorSeoPayloadAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Domain\Identity\Models\User;
use App\Domain\Seo\Enums\OgTypeEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ResolveAuthorSeoDataAction
{
    public function __construct(
        protected GetSeoDefaultsAction            $getSeoDefaultsAction,
        protected ResolveAuthorSeoPayloadAction   $resolveAuthorSeoPayloadAction,
        protected BuildAuthorSchemaGraphAction    $buildAuthorSchemaGraphAction,
        protected RenderSchemaGraphAction         $renderSchemaGraphAction,
        protected ResolveSeoPaginationLinksAction $resolveSeoPaginationLinksAction,
    )
    {
    }

    public function execute(
        User                  $author,
        ?LengthAwarePaginator $paginator = null,
        ?string               $paginationBaseUrl = null,
    ): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $payload = $this->resolveAuthorSeoPayloadAction->execute($author);

        $schemaGraph = $this->buildAuthorSchemaGraphAction->execute($author, $payload);
        $schemaJson = $this->renderSchemaGraphAction->execute($schemaGraph);

        return new SeoDataDTO(
            robots: $payload->robots ?: $defaults['robots'],
            title: $payload->title,
            description: $payload->description,
            canonicalUrl: $payload->canonicalUrl,
            author: null,
            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::PROFILE,
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
            ),
            schemaJson: $schemaJson,
            pagination: $paginator && $paginationBaseUrl
                ? $this->resolveSeoPaginationLinksAction->execute($paginator, $paginationBaseUrl)
                : null,
        );
    }
}
