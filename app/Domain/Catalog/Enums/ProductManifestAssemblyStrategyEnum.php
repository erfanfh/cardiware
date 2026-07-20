<?php

namespace App\Domain\Catalog\Enums;

enum ProductManifestAssemblyStrategyEnum: string
{
    /**
     * Every file is processed independently.
     */
    case INDEPENDENT = 'independent';

    /**
     * Files must be processed in their defined order.
     */
    case ORDERED = 'ordered';
}
