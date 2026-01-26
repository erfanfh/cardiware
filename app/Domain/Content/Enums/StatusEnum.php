<?php

namespace App\Domain\Content\Enums;

enum StatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case PENDING_REVIEW = 'pending_review';
}
