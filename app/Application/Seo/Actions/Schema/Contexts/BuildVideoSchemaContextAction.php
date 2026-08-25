<?php

namespace App\Application\Seo\Actions\Schema\Contexts;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Video\Models\Video;

final class BuildVideoSchemaContextAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $defaultsAction,
        private readonly SchemaIdGenerator $ids,
    ) {}

    public function execute(Video $video, ResolvedSeoPayloadDTO $payload): SchemaContextDTO
    {
        $defaults = $this->defaultsAction->execute();
        $logo = $defaults['organization_logo'];

        return new SchemaContextDTO(
            pageUrl: $payload->canonicalUrl,
            pageTitle: $payload->title,
            pageDescription: $payload->description,
            pageType: 'WebPage',
            siteUrl: $defaults['site_url'],
            siteName: $defaults['site_name'],
            siteAlternateName: get_setting('seo', 'site_alternate_name'),
            siteDescription: get_setting('seo', 'site_description'),
            inLanguage: (string) get_setting('seo', 'schema_language', 'fa-IR'),
            organizationName: get_setting('seo', 'organization_name', $defaults['site_name']),
            organizationAlternateName: get_setting('seo', 'organization_alternate_name'),
            organizationLegalName: get_setting('seo', 'organization_legal_name'),
            organizationEmail: get_setting('seo', 'organization_email'),
            organizationTelephone: get_setting('seo', 'organization_phone'),
            organizationFoundingDate: get_setting('seo', 'organization_founding_date'),
            organizationSameAs: $this->sameAs(get_setting('seo', 'organization_same_as', [])),
            organizationNumberOfEmployees: get_setting('seo', 'organization_number_of_employees'),
            logoUrl: $logo?->url,
            logoWidth: $logo?->primaryVariant?->width,
            logoHeight: $logo?->primaryVariant?->height,
            logoCaption: $defaults['site_name'],
            pageImageUrl: $payload->pageImageUrl,
            pageImageWidth: $payload->pageImageWidth,
            pageImageHeight: $payload->pageImageHeight,
            pageImageCaption: $payload->pageImageCaption,
            pageImageSchemaId: $payload->pageImageUrl ? $this->ids->primaryImage($payload->canonicalUrl) : null,
            headline: $payload->headline,
            publishedTime: $payload->publishedTime,
            modifiedTime: $payload->modifiedTime,
            commentCount: $payload->commentCount,
            keywords: $payload->keywords,
            articleSections: $payload->articleSections,
            commentUrl: $payload->canonicalUrl.'#comments',
            authorName: $payload->authorName,
            authorUrl: $payload->authorUrl,
            authorIdentifier: $payload->authorIdentifier,
            authorSameAs: $payload->authorSameAs,
        );
    }

    /** @return array<int, string> */
    private function sameAs(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/\r\n|\r|\n/', $value) ?: [];
        }

        return is_array($value)
            ? array_values(array_filter(array_map(static fn ($item) => is_string($item) ? trim($item) : null, $value)))
            : [];
    }
}
