<?php

namespace App\Infrastructure\Services\Audit;

use App\Domain\Audit\Enums\AuditEventTypeEnum;
use App\Domain\Audit\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    private const EXCLUDED = ['password', 'remember_token', 'token', 'secret', 'api_key', 'private_key'];

    public function record(Model $model, AuditEventTypeEnum $type, array $meta = []): void
    {
        if ($type === AuditEventTypeEnum::UPDATED) {
            $this->recordChange($model, $model->getPrevious(), $model->getChanges(), $meta);

            return;
        } elseif ($type === AuditEventTypeEnum::CREATED) {
            $old = null;
            $new = $this->snapshot($model);
        } elseif ($type === AuditEventTypeEnum::RESTORED) {
            $old = ['deleted_at' => $model->getPrevious()[$model->getDeletedAtColumn()] ?? null];
            $new = ['deleted_at' => null];
        } elseif ($type === AuditEventTypeEnum::DELETED || $type === AuditEventTypeEnum::FORCE_DELETED) {
            $old = $this->snapshot($model);
            $new = null;
        } else {
            $old = null;
            $new = null;
        }

        $this->persist($model, $type, $old, $new, $meta);
    }

    /** Use after an intentional raw update, while the old values are still available. */
    public function recordChange(Model $model, array $before, array $after, array $meta = []): void
    {
        $old = [];
        $new = [];
        foreach ($after as $key => $value) {
            if ($this->excluded($model, $key) || $key === $model->getUpdatedAtColumn()
                || ($before[$key] ?? null) === $value) {
                continue;
            }

            $old[$key] = $before[$key] ?? null;
            $new[$key] = $value;
        }

        if ($old !== []) {
            $this->persist($model, AuditEventTypeEnum::UPDATED, $old, $new, $meta);
        }
    }

    /** Record a many-to-many change, including changed pivot attributes. */
    public function sync(Model $model, string $relation, array $ids, bool $detaching = true): array
    {
        $related = $model->{$relation}();
        if (! $related instanceof BelongsToMany) {
            throw new \InvalidArgumentException("Relation [{$relation}] must be many-to-many.");
        }

        return $model->getConnection()->transaction(function () use ($model, $relation, $related, $ids, $detaching): array {
            $before = $this->pivotRows($related);
            $result = $related->sync($ids, $detaching);
            $this->recordPivotDiff($model, $relation, $related, $before);

            return $result;
        });
    }

    public function detach(Model $model, string $relation, array $ids): int
    {
        $related = $model->{$relation}();
        if (! $related instanceof BelongsToMany) {
            throw new \InvalidArgumentException("Relation [{$relation}] must be many-to-many.");
        }

        return $model->getConnection()->transaction(function () use ($model, $relation, $related, $ids): int {
            $before = $this->pivotRows($related);
            $count = $related->detach($ids);
            $this->recordPivotDiff($model, $relation, $related, $before);

            return $count;
        });
    }

    private function pivotRows(BelongsToMany $relation): array
    {
        $key = $relation->getRelatedPivotKeyName();

        return $relation->newPivotQuery()->get()->mapWithKeys(function (object $row) use ($key): array {
            $attributes = (array) $row;
            unset($attributes['created_at'], $attributes['updated_at']);
            $attributes = array_filter(
                $attributes,
                fn ($field) => preg_match('/(?:password|secret|token|private_key|api_key)/i', $field) !== 1,
                ARRAY_FILTER_USE_KEY,
            );

            return [(string) $attributes[$key] => $attributes];
        })->all();
    }

    private function recordPivotDiff(Model $model, string $name, BelongsToMany $relation, array $before): void
    {
        $after = $this->pivotRows($relation);
        $key = $relation->getRelatedPivotKeyName();
        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $id) {
            $oldRow = $before[$id] ?? null;
            $newRow = $after[$id] ?? null;
            if ($oldRow === $newRow) {
                continue;
            }

            $type = $oldRow === null ? AuditEventTypeEnum::ATTACHED : ($newRow === null ? AuditEventTypeEnum::DETACHED : AuditEventTypeEnum::UPDATED);
            $old = $oldRow === null ? null : array_filter($oldRow, fn ($value, $field) => ! array_key_exists($field, $newRow ?? []) || $newRow[$field] !== $value, ARRAY_FILTER_USE_BOTH);
            $new = $newRow === null ? null : array_filter($newRow, fn ($value, $field) => ! array_key_exists($field, $oldRow ?? []) || $oldRow[$field] !== $value, ARRAY_FILTER_USE_BOTH);
            $this->persist($model, $type, $old, $new, [
                'relation' => $name,
                'related_type' => $relation->getRelated()->getMorphClass(),
                'related_id' => $id,
                'pivot_key' => $key,
            ]);
        }
    }

    private function persist(Model $model, AuditEventTypeEnum $type, ?array $old, ?array $new, array $meta): void
    {
        // Some isolated tests intentionally create only the tables they exercise.
        if (app()->environment('testing') && ! $model->getConnection()->getSchemaBuilder()->hasTable('audit_events')) {
            return;
        }

        $user = Auth::user();
        $event = new AuditEvent([
            'type' => $type,
            'subject_id' => $model->getKey(),
            'subject_type' => $model->getMorphClass(),
            'subject_label' => $this->subjectLabel($model),
            'user_id' => $user?->getAuthIdentifier(),
            'actor_name' => $user
                ? mb_substr((string) ($user->display_name ?: $user->username ?: $user->getAuthIdentifier()), 0, 255)
                : (app()->runningInConsole() ? 'system' : 'guest'),
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : mb_substr((string) request()->userAgent(), 0, 255),
            'old_data' => $old,
            'new_data' => $new,
            'meta' => $meta ?: null,
        ]);
        $event->setConnection($model->getConnectionName());
        $event->save();
    }

    private function snapshot(Model $model): array
    {
        return array_filter($model->getAttributes(), fn ($key) => ! $this->excluded($model, $key), ARRAY_FILTER_USE_KEY);
    }

    private function subjectLabel(Model $model): ?string
    {
        $attributes = $model->getAttributes();
        foreach (['title', 'name', 'display_name', 'full_name', 'label', 'code', 'slug', 'username'] as $field) {
            $value = $attributes[$field] ?? null;
            if (is_scalar($value) && filled($value)) {
                return mb_substr((string) $value, 0, 160);
            }
        }

        return null;
    }

    private function excluded(Model $model, string $key): bool
    {
        return in_array(strtolower($key), self::EXCLUDED, true)
            || in_array($key, $model->getHidden(), true)
            || (method_exists($model, 'auditExcludedAttributes')
                && in_array($key, $model->auditExcludedAttributes(), true))
            || preg_match('/(?:password|secret|token|private_key|api_key)/i', $key) === 1;
    }
}
