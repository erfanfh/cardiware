<?php

namespace App\Domain\Audit\Traits;

use App\Domain\Audit\Observers\AuditableObserver;

/**
 * Trait Auditable
 *
 * When applied to an Eloquent model, this trait automatically
 * registers the AuditableObserver to capture and store audit logs
 * for the model lifecycle events.
 *
 * Usage:
 *   use Auditable;
 */
trait Auditable
{
    /**
     * Boot the auditable trait for the model.
     *
     * Registers the AuditableObserver.
     *
     * @return void
     */
    public static function bootAuditable(): void
    {
        static::observe(AuditableObserver::class);
    }
}
