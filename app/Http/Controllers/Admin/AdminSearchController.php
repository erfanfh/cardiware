<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

abstract class AdminSearchController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function permissionName(): string;

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorizeSearch();

        $query = trim((string)$request->string('q'));

        if (mb_strlen($query) < $this->minimumQueryLength()) {
            return response()->json([
                'items' => [],
            ]);
        }

        $items = $this->baseQuery()
            ->select($this->selectColumns())
            ->where(function (Builder $builder) use ($query): void {
                $this->applySearchConditions($builder, $query);
            })
            ->orderBy($this->orderByColumn(), $this->orderByDirection())
            ->limit($this->limit())
            ->get()
            ->map(fn(Model $model): array => [
                'value' => (string)$model->getKey(),
                'label' => $this->formatLabel($model),
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }

    protected function authorizeSearch(): void
    {
        Gate::authorize($this->permissionName());
    }

    protected function baseQuery(): Builder
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass();

        return $modelClass::query();
    }

    /**
     * @return array<int, string>
     */
    protected function selectColumns(): array
    {
        return [
            $this->keyColumn(),
            $this->labelColumn(),
        ];
    }

    protected function applySearchConditions(Builder $builder, string $query): void
    {
        $builder->where($this->labelColumn(), 'like', '%' . $query . '%');

        if (ctype_digit($query)) {
            $builder->orWhere($this->keyColumn(), (int)$query);
        }
    }

    protected function formatLabel(Model $model): string
    {
        $labelColumn = $this->labelColumn();

        return "{$model->{$labelColumn}} ({$model->getKey()})";
    }

    protected function keyColumn(): string
    {
        return 'id';
    }

    protected function labelColumn(): string
    {
        return 'title';
    }

    protected function orderByColumn(): string
    {
        return $this->labelColumn();
    }

    protected function orderByDirection(): string
    {
        return 'asc';
    }

    protected function minimumQueryLength(): int
    {
        return 2;
    }

    protected function limit(): int
    {
        return 50;
    }
}
