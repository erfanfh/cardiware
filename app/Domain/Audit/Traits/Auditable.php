<?php

namespace App\Domain\Audit\Traits;

use App\Domain\Audit\Observers\AuditableObserver;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::observe(AuditableObserver::class);
    }
}
