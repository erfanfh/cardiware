<?php

namespace App\Application\Slug\Actions;

use App\Application\Slug\DTOs\ResolvedSlugDTO;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Application\Slug\Services\SlugResolverManager;

class ResolveSlugAction
{
    public function __construct(
        private readonly SlugResolverManager $manager,
    )
    {
    }

    /**
     * Execute the slug resolution workflow.
     */
    public function execute(ResolveSlugDTO $dto): ResolvedSlugDTO
    {
        return $this->manager->resolve($dto);
    }
}
