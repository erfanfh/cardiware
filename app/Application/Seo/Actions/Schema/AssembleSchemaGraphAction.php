<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\Support\SchemaGraph;
use App\Application\Seo\Support\SchemaSanitizer;

final class AssembleSchemaGraphAction
{
    public function __construct(
        protected SchemaSanitizer $schemaSanitizer,
    )
    {
    }

    /**
     * @param array<string, mixed> $pageNode
     * @param array<string, mixed> $breadcrumbNode
     * @param array<string, mixed> $websiteNode
     * @param array<string, mixed> $organizationNode
     * @param array<int, array<string, mixed>> $extraNodes
     * @return array<string, mixed>
     */
    public function execute(
        array $pageNode,
        array $breadcrumbNode,
        array $websiteNode,
        array $organizationNode,
        array $extraNodes = [],
    ): array
    {
        $graph = new SchemaGraph();

        foreach ($extraNodes as $extraNode) {
            $graph->add($extraNode);
        }

        $graph
            ->add($pageNode)
            ->add($breadcrumbNode)
            ->add($websiteNode)
            ->add($organizationNode);

        return $this->schemaSanitizer->sanitize($graph->toArray());
    }
}
