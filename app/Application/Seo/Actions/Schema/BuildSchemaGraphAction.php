<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\Contracts\SeoPageSchemaNodeInterface;
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
        protected SchemaSanitizer                      $schemaSanitizer,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(SchemaContextDTO $context, SeoPageSchemaNodeInterface $pageNodeAction): array
    {
        $logoNode = $this->buildLogoImageSchemaAction->execute($context);
        $numberOfEmployeesNode = $this->buildNumberOfEmployeesSchemaAction->execute($context);

        $organizationNode = $this->buildOrganizationSchemaAction->execute($context, $logoNode, $numberOfEmployeesNode);

        $searchActionNode = $this->buildWebsiteSearchActionSchemaAction->execute(
            searchUrlTemplate: route('search') . '/?q={search_term_string}',
            queryParameterName: 'search_term_string',
        );

        $websiteNode = $this->buildWebsiteSchemaAction->execute(
            $context,
            $organizationNode,
            $searchActionNode,
        );

        $breadcrumbNode = $this->buildBreadcrumbSchemaAction->execute($context);

        $pageNode = $pageNodeAction->execute(
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
