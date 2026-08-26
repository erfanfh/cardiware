<?php

namespace App\Application\Seo\Actions\Schema\Contexts;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Domain\Poll\Models\Poll;

final class BuildPollSchemaContextAction
{
    public function __construct(private readonly GetSeoDefaultsAction $defaultsAction) {}

    public function execute(
        Poll $poll,
        string $canonicalUrl,
        string $pageName,
        string $description,
    ): SchemaContextDTO {
        $defaults = $this->defaultsAction->execute();
        $logo = $defaults['organization_logo'];

        return new SchemaContextDTO(
            pageUrl: $canonicalUrl,
            pageTitle: $pageName,
            pageDescription: $description,
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
            headline: $pageName,
            publishedTime: $poll->created_at?->utc()->toIso8601String(),
            modifiedTime: $poll->updated_at?->utc()->toIso8601String(),
        );
    }

    /** @return array<int, string> */
    private function sameAs(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/\r\n|\r|\n/', $value) ?: [];
        }

        return is_array($value)
            ? array_values(array_filter(array_map(
                static fn ($item) => is_string($item) ? trim($item) : null,
                $value,
            )))
            : [];
    }
}
