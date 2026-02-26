<?php

namespace App\Infrastructure\Services\Audit;

use App\Domain\Audit\Enums\AuditEventTypeEnum;
use App\Domain\Audit\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AuditService
{
    /**
     * Attributes that should never be stored in audit.
     *
     * @var array<int, string>
     */
    protected array $excludedAttributes = [
        'password',
        'remember_token',
    ];

    /**
     * Record attached/detached pivot changes for a model's relation.
     *
     * This method loops through the 'attached' and 'detached' IDs returned by
     * a `sync` operation on a BelongsToMany or MorphToMany relation, and
     * creates separate audit events for each change.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The model instance being audited.
     * @param string $relation The name of the relation or pivot table for audit purposes.
     * @param array $result The result of a `sync` call, must contain 'attached' and/or 'detached' keys.
     * @param string $foreignKeyName The name of the pivot foreign key to include in the audit meta.
     * @param array<string, mixed> $extraMeta Optional additional metadata to include in each audit record.
     *
     * @return void
     */
    public function recordSync(Model $model, string $relation, array $result, string $foreignKeyName, array $extraMeta = []): void
    {
        if (empty($result['attached']) && empty($result['detached'])) {
            return;
        }

        if (!empty($result['attached'])) {
            foreach ($result['attached'] as $id) {
                $this->record($model, AuditEventTypeEnum::ATTACHED, array_merge([
                    'relation' => $relation,
                    $foreignKeyName => $id,
                ], $extraMeta));
            }
        }

        if (!empty($result['detached'])) {
            foreach ($result['detached'] as $id) {
                $this->record($model, AuditEventTypeEnum::DETACHED, array_merge([
                    'relation' => $relation,
                    $foreignKeyName => $id,
                ], $extraMeta));
            }
        }
    }


    /**
     * Record an audit event for the given model and type.
     *
     *
     * @param Model $model The domain entity being audited.
     * @param AuditEventTypeEnum $type The type of audit event.
     * @param array<string, mixed> $meta Optional contextual metadata.
     *
     * @return void
     */
    public function record(Model $model, AuditEventTypeEnum $type, array $meta = []): void
    {
        match ($type) {
            AuditEventTypeEnum::CREATED => $this->handleCreated($model),
            AuditEventTypeEnum::UPDATED => $this->handleUpdated($model),
            AuditEventTypeEnum::DELETED,
            AuditEventTypeEnum::FORCE_DELETED => $this->handleDeleted($model, $type),
            AuditEventTypeEnum::RESTORED => $this->handleRestored($model),
            AuditEventTypeEnum::ATTACHED,
            AuditEventTypeEnum::DETACHED => $this->handleRelationChange($model, $type, $meta),
        };
    }

    /**
     * Handle create event.
     *
     * Storage policy:
     *  - old_data = null
     *  - new_data = full snapshot of the record
     */
    protected function handleCreated(Model $model): void
    {
        $this->log(
            model: $model,
            type: AuditEventTypeEnum::CREATED,
            oldData: null,
            newData: $this->snapshot($model)
        );
    }

    /**
     * Persist audit event record into storage.
     *
     * @param Model $model
     * @param AuditEventTypeEnum $type
     * @param array<string, mixed>|null $oldData
     * @param array<string, mixed>|null $newData
     * @param array<string, mixed>|null $meta
     *
     * @return void
     */
    protected function log(
        Model              $model,
        AuditEventTypeEnum $type,
        ?array             $oldData,
        ?array             $newData,
        ?array             $meta = null,
    ): void
    {
        AuditEvent::create([
            'type' => $type->value,
            'subject_id' => $model->getKey(),
            'subject_type' => $model->getMorphClass(),
            'user_id' => auth()->id(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'old_data' => $oldData,
            'new_data' => $newData,
            'meta' => $meta,
        ]);
    }

    /**
     * Create a sanitized snapshot of the model attributes.
     *
     * @param Model $model
     * @return array<string, mixed>
     */
    protected function snapshot(Model $model): array
    {
        $attributes = $model->attributesToArray();

        return Arr::except($attributes, $this->excludedAttributes);
    }

    /**
     * Handle update event.
     *
     * Storage policy:
     *  - old_data = only changed fields before update
     *  - new_data = only changed fields after update
     */
    protected function handleUpdated(Model $model): void
    {
        $dirty = $model->getDirty();

        if (empty($dirty)) {
            return;
        }

        $old = [];
        $new = [];

        foreach ($dirty as $field => $value) {
            if ($this->isExcluded($field)) {
                continue;
            }

            $old[$field] = $model->getOriginal($field);
            $new[$field] = $value;
        }

        if (empty($old) && empty($new)) {
            return;
        }

        $this->log(
            model: $model,
            type: AuditEventTypeEnum::UPDATED,
            oldData: $old,
            newData: $new,
        );
    }

    /**
     * Determine whether an attribute should be excluded from audit.
     *
     * @param string $field
     * @return bool
     */
    protected function isExcluded(string $field): bool
    {
        return in_array($field, $this->excludedAttributes, true);
    }

    /**
     * Handle soft/force delete event.
     *
     * Storage policy:
     *  - old_data = full snapshot before deletion
     *  - new_data = null
     */
    protected function handleDeleted(Model $model, AuditEventTypeEnum $type): void
    {
        $this->log(
            model: $model,
            type: $type,
            oldData: $this->snapshot($model),
            newData: null,
            meta: [
                'deleted_by' => auth()->id(),
            ]
        );
    }

    /**
     * Handle restore event.
     *
     * Storage policy:
     *  - old_data = deleted_at delta
     *  - new_data = reset values (null)
     */
    protected function handleRestored(Model $model): void
    {
        $this->log(
            model: $model,
            type: AuditEventTypeEnum::RESTORED,
            oldData: [
                'deleted_at' => $model->deleted_at,
                'deleted_by' => $model->deleted_by ?? auth()->id(),
            ],
            newData: [
                'deleted_at' => null,
                'deleted_by' => null,
            ]
        );
    }

    /**
     * Handle relation attach/detach event.
     *
     * Storage policy:
     *  - old_data = null
     *  - new_data = null
     *  - meta contains relation context
     *
     * @param array<string, mixed> $meta
     */
    protected function handleRelationChange(Model $model, AuditEventTypeEnum $type, array $meta): void
    {
        $this->log(
            model: $model,
            type: $type,
            oldData: null,
            newData: null,
            meta: $meta
        );
    }
}
