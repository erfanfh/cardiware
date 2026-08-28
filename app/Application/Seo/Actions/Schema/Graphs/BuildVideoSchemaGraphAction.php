<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildVideoBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPersonSchemaAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\BuildVideoObjectSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildVideoSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildVideoPageSchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Domain\Video\Models\Video;

final class BuildVideoSchemaGraphAction
{
    public function __construct(
        private readonly BuildVideoSchemaContextAction $contextAction,
        private readonly BuildBaseSchemaNodesAction $baseNodesAction,
        private readonly BuildPrimaryImageSchemaAction $imageAction,
        private readonly BuildPersonSchemaAction $personAction,
        private readonly BuildVideoBreadcrumbSchemaAction $breadcrumbAction,
        private readonly BuildVideoObjectSchemaAction $videoAction,
        private readonly BuildVideoPageSchemaAction $pageAction,
        private readonly AssembleSchemaGraphAction $assembleAction,
    ) {}

    /** @return array<string, mixed> */
    public function execute(Video $video, ResolvedSeoPayloadDTO $payload): array
    {
        $context = $this->contextAction->execute($video, $payload);
        $base = $this->baseNodesAction->execute($context);
        $image = $this->imageAction->execute($context);
        $person = $this->personAction->execute($context);
        $breadcrumb = $this->breadcrumbAction->execute($video, $context);
        $videoNode = $this->videoAction->execute($video, $context, $base->organizationNode, $person, $image);
        $page = $this->pageAction->execute($context, $base->websiteNode, $breadcrumb, $videoNode);

        return $this->assembleAction->execute(
            pageNode: $page,
            breadcrumbNode: $breadcrumb,
            websiteNode: $base->websiteNode,
            organizationNode: $base->organizationNode,
            extraNodes: [$videoNode, $image, $person],
        );
    }
}
