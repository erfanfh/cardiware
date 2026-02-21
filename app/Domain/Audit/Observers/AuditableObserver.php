<?php

namespace App\Domain\Audit\Observers;

use App\Domain\Audit\Enums\AuditEventTypeEnum;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer responsible for automatically recording audit events
 * for Eloquent models that use the Auditable trait.
 *
 * This observer listens to model lifecycle events and delegates audit logging to the AuditService.
 *
 * It also handles soft delete edge cases to avoid incorrect audit entries
 * during restore operations.
 */
class AuditableObserver
{
    protected AuditService $auditService;

    /**
     * Create a new observer instance.
     *
     * @param AuditService $auditService
     */
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle the model "created" event.
     *
     * @param Model $model
     * @return void
     */
    public function created(Model $model): void
    {
        $this->auditService->record($model, AuditEventTypeEnum::CREATED);
    }

    /**
     * Handle the model "updated" event.
     *
     * Prevents false update logs during soft delete restore flow.
     *
     * @param Model $model
     * @return void
     */
    public function updated(Model $model): void
    {
        if (array_key_exists('deleted_at', $model->getDirty()) &&
            $model->getOriginal('deleted_at') !== null
            && $model->deleted_at === null) {
            return;
        }

        $this->auditService->record($model, AuditEventTypeEnum::UPDATED);
    }

    /**
     * Handle the model "deleted" event.
     *
     * Detects whether the deletion is soft or force delete.
     *
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model): void
    {
        $type = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
            ? AuditEventTypeEnum::FORCE_DELETED
            : AuditEventTypeEnum::DELETED;

        $this->auditService->record($model, $type);
    }

    /**
     * Handle the model "restoring" event.
     *
     * @param Model $model
     * @return void
     */
    public function restoring(Model $model): void
    {
        $this->auditService->record($model, AuditEventTypeEnum::RESTORED);
    }
}
