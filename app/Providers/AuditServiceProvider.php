<?php

namespace App\Providers;

use App\Domain\Audit\Enums\AuditEventTypeEnum;
use App\Domain\Audit\Models\AuditEvent;
use App\Domain\Audit\Traits\Auditable;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (['created', 'updated', 'deleted', 'restored'] as $event) {
            Event::listen("eloquent.{$event}: *", function (string $name, array $payload) use ($event): void {
                static $traitCache = [];
                $model = $payload[0] ?? null;
                if (! $model instanceof Model || $model instanceof AuditEvent
                    || ! str_starts_with($model::class, 'App\\Domain\\')
                    || ($traitCache[$model::class] ??= in_array(Auditable::class, class_uses_recursive($model), true))) {
                    return;
                }

                if ($event === 'updated' && method_exists($model, 'getDeletedAtColumn')
                    && array_key_exists($model->getDeletedAtColumn(), $model->getChanges())
                    && ($model->getPrevious()[$model->getDeletedAtColumn()] ?? null) !== null
                    && $model->getAttribute($model->getDeletedAtColumn()) === null) {
                    return;
                }

                $type = match ($event) {
                    'created' => AuditEventTypeEnum::CREATED,
                    'updated' => AuditEventTypeEnum::UPDATED,
                    'restored' => AuditEventTypeEnum::RESTORED,
                    default => method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
                        ? AuditEventTypeEnum::FORCE_DELETED : AuditEventTypeEnum::DELETED,
                };

                app(AuditService::class)->record($model, $type);
            });
        }
    }
}
