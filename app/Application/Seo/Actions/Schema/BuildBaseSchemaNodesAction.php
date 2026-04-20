<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\BaseSchemaNodesDTO;
use App\Application\Seo\DTOs\SchemaContextDTO;

final class BuildBaseSchemaNodesAction
{
    public function __construct(
        protected BuildLogoImageSchemaAction           $buildLogoImageSchemaAction,
        protected BuildNumberOfEmployeesSchemaAction   $buildNumberOfEmployeesSchemaAction,
        protected BuildOrganizationSchemaAction        $buildOrganizationSchemaAction,
        protected BuildWebsiteSearchActionSchemaAction $buildWebsiteSearchActionSchemaAction,
        protected BuildWebsiteSchemaAction             $buildWebsiteSchemaAction,
    )
    {
    }

    public function execute(SchemaContextDTO $context): BaseSchemaNodesDTO
    {
        $logoNode = $this->buildLogoImageSchemaAction->execute($context);

        $numberOfEmployeesNode = $this->buildNumberOfEmployeesSchemaAction->execute($context);

        $organizationNode = $this->buildOrganizationSchemaAction->execute(
            $context,
            $logoNode,
            $numberOfEmployeesNode,
        );

        $searchActionNode = $this->buildWebsiteSearchActionSchemaAction->execute(
            searchUrlTemplate: route('search', absolute: true) . '/?q={search_term_string}',
            queryParameterName: 'search_term_string',
        );

        $websiteNode = $this->buildWebsiteSchemaAction->execute(
            $context,
            $organizationNode,
            $searchActionNode,
        );

        return new BaseSchemaNodesDTO(
            logoNode: $logoNode,
            numberOfEmployeesNode: $numberOfEmployeesNode,
            organizationNode: $organizationNode,
            searchActionNode: $searchActionNode,
            websiteNode: $websiteNode,
        );
    }
}
