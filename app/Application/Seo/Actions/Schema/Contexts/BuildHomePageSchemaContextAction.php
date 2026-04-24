<?php

namespace App\Application\Seo\Actions\Schema\Contexts;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Domain\Media\Models\MediaFile;

final class BuildHomePageSchemaContextAction
{
    public function __construct(
        protected GetSeoDefaultsAction $getSeoDefaultsAction,
    )
    {
    }

    public function execute(ResolvedSeoPayloadDTO $payload): SchemaContextDTO
    {
        $siteName = $this->getSeoDefaultsAction->execute()['site_name'] ?? null;
        $siteUrl = $this->getSeoDefaultsAction->execute()['site_url'] ?? null;

        $logoSetting = get_setting('seo', 'organization_logo');
        $logo = null;

        if ($logoSetting?->id) {
            $logo = MediaFile::with('primaryVariant')->find($logoSetting->id);
        }

        return new SchemaContextDTO(
            pageUrl: $payload->canonicalUrl,
            pageTitle: $payload->title,
            pageDescription: $payload->description,
            pageType: 'CollectionPage',

            siteUrl: $siteUrl,
            siteName: (string)$siteName,
            siteAlternateName: get_setting('seo', 'site_alternate_name'),
            siteDescription: get_setting('seo', 'site_description'),
            inLanguage: (string)get_setting('seo', 'schema_language', 'fa-IR'),

            organizationName: get_setting('seo', 'organization_name', get_setting('seo', 'site_name', config('app.name'))),
            organizationAlternateName: get_setting('seo', 'organization_alternate_name'),
            organizationLegalName: get_setting('seo', 'organization_legal_name'),
            organizationEmail: get_setting('seo', 'organization_email'),
            organizationTelephone: get_setting('seo', 'organization_phone'),
            organizationFoundingDate: get_setting('seo', 'organization_founding_date'),
            organizationSameAs: $this->normalizeSameAs(
                get_setting('seo', 'organization_same_as', [])
            ),
            organizationNumberOfEmployees: get_setting('seo', 'organization_number_of_employees'),

            logoUrl: $logo?->url,
            logoWidth: $logo?->primaryVariant?->width,
            logoHeight: $logo?->primaryVariant?->height,
            logoCaption: $siteName,
        );
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    protected function normalizeSameAs(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/\r\n|\r|\n/', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn($item) => is_string($item) ? trim($item) : null,
            $value
        )));
    }
}
