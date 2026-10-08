<?php

namespace App\Domain\Audit\Actions;

use App\Infrastructure\Services\Audit\AuditService;
use Closure;
use Illuminate\Database\Eloquent\Model;

class SyncRelationsAction
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * @param  iterable<int|string>  $items
     * @return array{attached: array, detached: array, updated: array}
     */
    public function execute(Model $model, string $relation, iterable $items, ?Closure $pivotBuilder = null): array
    {
        $syncData = collect($items)->mapWithKeys(fn ($item) => [
            $item => $pivotBuilder ? $pivotBuilder($item) : [],
        ]);

        return $this->auditService->sync($model, $relation, $syncData->all());
    }
}
