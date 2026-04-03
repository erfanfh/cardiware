<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Domain\Identity\Models\Permission;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionSearchController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string)$request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'items' => [],
            ]);
        }

        $items = Permission::query()
            ->select(['id', 'title'])
            ->where('title', 'like', '%' . $query . '%')
            ->orderBy('title')
            ->limit(20)
            ->get()
            ->map(fn(Permission $permission) => [
                'value' => (string)$permission->id,
                'label' => $permission->title,
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
