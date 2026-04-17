<?php

namespace App\Application\Seo\Contracts;

use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\Support\SeoContext;

interface SeoResolverInterface
{
    public function supports(SeoContext $context): bool;

    public function resolve(SeoContext $context): SeoDataDTO;
}
