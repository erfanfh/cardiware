<?php

namespace App\Domain\Audit\Actions;

use App\Infrastructure\Services\Audit\AuditService;
use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic action for synchronizing model relations
 * while automatically recording audit events.
 *
 * This action wraps laravel's 'sync()' behavior and ensures
 * that attach/detach operations are properly logged.
 */
class SyncRelationsAction
{
    public function __construct(
        protected AuditService $auditService
    )
    {
    }

    /**
     * Execute relation synchronization with audit logging.
     *
     * @param Model $model
     * @param string $relation
     * @param iterable $items
     * @param Closure|null $pivotBuilder fn($item): array
     * @param string $auditRelationName
     * @param string $foreignKeyName
     * @param array<string,mixed> $auditMeta
     *
     * @return array{attached: array, detached: array, updated: array}
     */
    public function execute(
        Model    $model,
        string   $relation,
        iterable $items,
        ?Closure $pivotBuilder,
        string   $auditRelationName,
        string   $foreignKeyName,
        array    $auditMeta = [],
    ): array
    {

        $syncData = collect($items)->mapWithKeys(function ($item) use ($pivotBuilder) {
            return [
                $item => $pivotBuilder ? $pivotBuilder($item) : [],
            ];
        });

        $result = $model->{$relation}()->sync($syncData);

        $this->auditService->recordSync(
            $model,
            $auditRelationName,
            $result,
            $foreignKeyName,
            $auditMeta
        );

        return $result;
    }
}
