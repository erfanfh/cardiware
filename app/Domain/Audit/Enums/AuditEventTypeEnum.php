<?php

namespace App\Domain\Audit\Enums;

enum AuditEventTypeEnum: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
}
