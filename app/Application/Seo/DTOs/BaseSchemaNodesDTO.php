<?php

namespace App\Application\Seo\DTOs;

final class BaseSchemaNodesDTO
{
    public function __construct(
        public readonly array $logoNode = [],
        public readonly array $numberOfEmployeesNode = [],
        public readonly array $organizationNode = [],
        public readonly array $searchActionNode = [],
        public readonly array $websiteNode = [],
    )
    {
    }
}
