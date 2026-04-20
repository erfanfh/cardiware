<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPersonSchemaAction;
use App\Application\Seo\Actions\Schema\BuildPostArticleSchemaAction;
use App\Application\Seo\Actions\Schema\BuildPostBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildPostSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildPostSchemaAction;
use App\Domain\Content\Models\Post;

final class BuildPostSchemaGraphAction
{
    public function __construct(
        protected BuildPostSchemaContextAction    $buildPostSchemaContextAction,
        protected BuildBaseSchemaNodesAction      $buildBaseSchemaNodesAction,
        protected BuildPrimaryImageSchemaAction   $buildPrimaryImageSchemaAction,
        protected BuildPersonSchemaAction         $buildPersonSchemaAction,
        protected BuildPostArticleSchemaAction    $buildPostArticleSchemaAction,
        protected BuildPostBreadcrumbSchemaAction $buildPostBreadcrumbSchemaAction,
        protected BuildPostSchemaAction           $buildPostSchemaAction,
        protected AssembleSchemaGraphAction       $buildSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Post $post): array
    {
        $context = $this->buildPostSchemaContextAction->execute($post);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);

        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);
        $personNode = $this->buildPersonSchemaAction->execute($context);
        $breadcrumbNode = $this->buildPostBreadcrumbSchemaAction->execute($context);

        $articleNode = $this->buildPostArticleSchemaAction->execute(
            $context,
            $baseNodes->organizationNode,
            $personNode,
            $primaryImageNode,
        );

        $pageNode = $this->buildPostSchemaAction->execute(
            $context,
            $baseNodes->websiteNode,
            $baseNodes->organizationNode,
            $breadcrumbNode,
        );

        return $this->buildSchemaGraphAction->execute(
            pageNode: $pageNode,
            breadcrumbNode: $breadcrumbNode,
            websiteNode: $baseNodes->websiteNode,
            organizationNode: $baseNodes->organizationNode,
            extraNodes: [
                $articleNode,
                $primaryImageNode,
                $personNode,
            ],
        );
    }
}
