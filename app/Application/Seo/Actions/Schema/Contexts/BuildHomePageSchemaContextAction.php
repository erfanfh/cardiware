<?php

namespace App\Application\Seo\Actions\Schema\Contexts;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Domain\Media\Models\MediaFile;

final class BuildHomePageSchemaContextAction
{
    public function __construct(
        protected ResolveCanonicalUrlAction $resolveCanonicalUrlAction,
    )
    {
    }

    public function execute(): SchemaContextDTO
    {
        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            customCanonicalUrl: url('/'),
        );

        $siteName = app(GetSeoDefaultsAction::class)->execute()['site_name'] ?? null;
        $logo = MediaFile::with('primaryVariant')->where('id', get_setting('seo', 'organization_logo')->id)->first();

        return new SchemaContextDTO(
            pageUrl: $canonicalUrl,
            pageTitle: (string)get_setting('seo', 'home_title'),
            pageDescription: get_setting('seo', 'home_description'),
            siteUrl: $canonicalUrl,
            siteName: (string)$siteName,
            siteAlternateName: get_setting('seo', 'site_alternate_name'),
            siteDescription: get_setting('seo', 'site_description'),
            inLanguage: (string)get_setting('seo', 'schema_language', 'fa-IR'),
            pageType: 'CollectionPage',
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
            logoUrl: $logo->url,
            logoWidth: $logo->primaryVariant?->width,
            logoHeight: $logo->primaryVariant?->height,
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
