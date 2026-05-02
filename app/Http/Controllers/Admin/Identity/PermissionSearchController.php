<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Domain\Identity\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PermissionSearchController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        Gate::authorize('role.list');

        $query = trim((string)$request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'items' => [],
            ]);
        }

        $items = Permission::query()
            ->select(['id', 'label'])
            ->where(function ($builder) use ($query) {
                $builder->where('label', 'like', '%' . $query . '%')
                    ->orWhere('id', $query);
            })
            ->orderBy('label')
            ->limit(50)
            ->get()
            ->map(fn(Permission $permission) => [
                'value' => (string)$permission->id,
                'label' => "$permission->label ($permission->id)",
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
