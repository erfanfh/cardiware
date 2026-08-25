<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Video\Models\Video;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class BuildVideoCollectionSchemaGraphAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $defaultsAction,
        private readonly BuildBaseSchemaNodesAction $baseNodesAction,
        private readonly AssembleSchemaGraphAction $assembleAction,
        private readonly SchemaIdGenerator $ids,
    ) {}

    /** @return array<string, mixed> */
    public function execute(string $url, string $title, string $description, ?LengthAwarePaginator $paginator = null, ?TaxonomyTerm $term = null): array
    {
        $defaults = $this->defaultsAction->execute();
        $logo = $defaults['organization_logo'];
        $context = new SchemaContextDTO(
            pageUrl: $url,
            pageTitle: $title,
            pageDescription: $description,
            pageType: 'CollectionPage',
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
            headline: $term?->title ?: 'ویدیوها',
        );

        $base = $this->baseNodesAction->execute($context);
        $breadcrumb = $this->breadcrumb($context, $term);
        $itemList = $this->itemList($context, $paginator);
        $page = [
            '@type' => 'CollectionPage',
            '@id' => $url,
            'url' => $url,
            'name' => $title,
            'description' => $description,
            'isPartOf' => isset($base->websiteNode['@id']) ? ['@id' => $base->websiteNode['@id']] : null,
            'breadcrumb' => ['@id' => $breadcrumb['@id']],
            'mainEntity' => $itemList ? ['@id' => $itemList['@id']] : null,
            'inLanguage' => $context->inLanguage,
        ];

        return $this->assembleAction->execute(
            pageNode: $page,
            breadcrumbNode: $breadcrumb,
            websiteNode: $base->websiteNode,
            organizationNode: $base->organizationNode,
            extraNodes: [$itemList],
        );
    }

    /** @return array<string, mixed> */
    private function breadcrumb(SchemaContextDTO $context, ?TaxonomyTerm $term): array
    {
        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => $context->siteUrl],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'ویدیوها', 'item' => route('videos.index')],
        ];
        if ($term) {
            $items[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $term->title];
        }

        return ['@type' => 'BreadcrumbList', '@id' => $this->ids->breadcrumb($context->pageUrl), 'itemListElement' => $items];
    }

    /** @return array<string, mixed> */
    private function itemList(SchemaContextDTO $context, ?LengthAwarePaginator $paginator): array
    {
        if (! $paginator || $paginator->isEmpty()) {
            return [];
        }

        $offset = ($paginator->currentPage() - 1) * $paginator->perPage();
        $items = collect($paginator->items())->values()->map(
            static fn (Video $video, int $index) => array_filter([
                '@type' => 'ListItem',
                'position' => $offset + $index + 1,
                'url' => $video->url,
                'name' => $video->title,
                'image' => $video->cover?->url,
            ], static fn ($value) => ! is_null($value) && $value !== '')
        )->all();

        return [
            '@type' => 'ItemList',
            '@id' => $this->ids->itemList($context->pageUrl),
            'name' => $context->headline,
            'numberOfItems' => $paginator->total(),
            'itemListOrder' => 'https://schema.org/ItemListOrderDescending',
            'itemListElement' => $items,
        ];
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
