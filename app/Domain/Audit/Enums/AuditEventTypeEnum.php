<?php

namespace App\Domain\Audit\Enums;

enum AuditEventTypeEnum: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case RESTORED = 'restored';
    case FORCE_DELETED = 'force_deleted';
    case ATTACHED = 'attached';
    case DETACHED = 'detached';
}
