<?php

namespace App\Domain\Taxonomy\Enums;

enum TaxonomyAdminTemplateEnum: string
{
    case HIERARCHICAL = 'hierarchical';
    case FLAT = 'flat';
}
