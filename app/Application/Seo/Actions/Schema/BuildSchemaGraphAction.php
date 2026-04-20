<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaGraph;
use App\Application\Seo\Support\SchemaSanitizer;

final class BuildSchemaGraphAction
{
    public function __construct(
        protected BuildLogoImageSchemaAction           $buildLogoImageSchemaAction,
        protected BuildOrganizationSchemaAction        $buildOrganizationSchemaAction,
        protected BuildNumberOfEmployeesSchemaAction   $buildNumberOfEmployeesSchemaAction,
        protected BuildWebsiteSearchActionSchemaAction $buildWebsiteSearchActionSchemaAction,
        protected BuildWebsiteSchemaAction             $buildWebsiteSchemaAction,
        protected BuildBreadcrumbSchemaAction          $buildBreadcrumbSchemaAction,
        protected BuildHomePageSchemaAction            $buildHomePageSchemaAction,
        protected SchemaSanitizer                      $schemaSanitizer,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(SchemaContextDTO $context): array
    {
        $logoNode = $this->buildLogoImageSchemaAction->handle($context);
        $numberOfEmployeesNode = $this->buildNumberOfEmployeesSchemaAction->handle($context);

        $organizationNode = $this->buildOrganizationSchemaAction->handle($context, $logoNode, $numberOfEmployeesNode);

        $searchActionNode = $this->buildWebsiteSearchActionSchemaAction->handle(
            searchUrlTemplate: route('search') . '/?q={search_term_string}',
            queryParameterName: 'search_term_string',
        );

        $websiteNode = $this->buildWebsiteSchemaAction->handle(
            $context,
            $organizationNode,
            $searchActionNode,
        );

        $breadcrumbNode = $this->buildBreadcrumbSchemaAction->handle($context);

        $pageNode = $this->buildHomePageSchemaAction->handle(
            $context,
            $websiteNode,
            $organizationNode,
            $breadcrumbNode,
        );

        $graph = (new SchemaGraph())
            ->add($pageNode)
            ->add($breadcrumbNode)
            ->add($websiteNode)
            ->add($organizationNode)
            ->toArray();

        return $this->schemaSanitizer->sanitize($graph);
    }
}
