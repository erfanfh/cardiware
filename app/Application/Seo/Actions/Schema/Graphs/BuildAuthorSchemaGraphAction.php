<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildAuthorBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildAuthorPersonSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildAuthorSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildAuthorSchemaAction;
use App\Application\Seo\DTOs\ResolvedAuthorSeoPayloadDTO;
use App\Domain\Identity\Models\User;

final class BuildAuthorSchemaGraphAction
{
    public function __construct(
        protected BuildAuthorSchemaContextAction    $buildAuthorSchemaContextAction,
        protected BuildBaseSchemaNodesAction        $buildBaseSchemaNodesAction,
        protected BuildPrimaryImageSchemaAction     $buildPrimaryImageSchemaAction,
        protected BuildAuthorBreadcrumbSchemaAction $buildAuthorBreadcrumbSchemaAction,
        protected BuildAuthorSchemaAction           $buildAuthorSchemaAction,
        protected BuildAuthorPersonSchemaAction     $buildAuthorPersonSchemaAction,
        protected AssembleSchemaGraphAction         $assembleSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(User $author, ResolvedAuthorSeoPayloadDTO $payload): array
    {
        $context = $this->buildAuthorSchemaContextAction->execute($author, $payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);

        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);
        $breadcrumbNode = $this->buildAuthorBreadcrumbSchemaAction->execute($context);

        $pageNode = $this->buildAuthorSchemaAction->execute(
            $context,
            $baseNodes->websiteNode,
            $baseNodes->organizationNode,
            $breadcrumbNode,
        );

        $personNode = $this->buildAuthorPersonSchemaAction->execute(
            $author,
            $context,
            $pageNode,
        );

        return $this->assembleSchemaGraphAction->execute(
            pageNode: $pageNode,
            breadcrumbNode: $breadcrumbNode,
            websiteNode: $baseNodes->websiteNode,
            organizationNode: $baseNodes->organizationNode,
            extraNodes: array_values(array_filter([
                $primaryImageNode,
                $personNode,
            ])),
        );
    }
}
