<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildPollBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildPollSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildPollPageSchemaAction;
use App\Domain\Poll\Models\Poll;

final class BuildPollSchemaGraphAction
{
    public function __construct(
        private readonly BuildPollSchemaContextAction $contextAction,
        private readonly BuildBaseSchemaNodesAction $baseNodesAction,
        private readonly BuildPollBreadcrumbSchemaAction $breadcrumbAction,
        private readonly BuildPollPageSchemaAction $pageAction,
        private readonly AssembleSchemaGraphAction $assembleAction,
    ) {}

    /** @return array<string, mixed> */
    public function execute(
        Poll $poll,
        string $canonicalUrl,
        string $pageName,
        string $description,
    ): array {
        $context = $this->contextAction->execute($poll, $canonicalUrl, $pageName, $description);
        $base = $this->baseNodesAction->execute($context);
        $breadcrumb = $this->breadcrumbAction->execute($context);
        $page = $this->pageAction->execute($context, $base->websiteNode, $breadcrumb);

        return $this->assembleAction->execute(
            pageNode: $page,
            breadcrumbNode: $breadcrumb,
            websiteNode: $base->websiteNode,
            organizationNode: $base->organizationNode,
        );
    }
}
