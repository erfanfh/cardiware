<?php

namespace App\Domain\Taxonomy\Enums;

enum TaxonomyTermTypeEnum: string
{
    case CATEGORY = 'category';
    case TAG = 'tag';
}
