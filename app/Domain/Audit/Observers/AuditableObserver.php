<?php

namespace App\Domain\Audit\Observers;

use App\Domain\Audit\Enums\AuditEventTypeEnum;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function created(Model $model): void
    {
        $this->auditService->record($model, AuditEventTypeEnum::CREATED);
    }

    public function updated(Model $model): void
    {
        if (array_key_exists('deleted_at', $model->getDirty()) &&
            $model->getOriginal('deleted_at') !== null
            && $model->deleted_at === null) {
            return;
        }

        $this->auditService->record($model, AuditEventTypeEnum::UPDATED);
    }

    public function deleted(Model $model): void
    {
        $type = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
            ? AuditEventTypeEnum::FORCE_DELETED
            : AuditEventTypeEnum::DELETED;

        $this->auditService->record($model, $type);
    }

    public function restoring(Model $model): void
    {
        $this->auditService->record($model, AuditEventTypeEnum::RESTORED);
    }
}
