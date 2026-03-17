<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategorySearchController extends Controller
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

        $items = TaxonomyTerm::query()
            ->category()
            ->select(['id', 'title'])
            ->where('title', 'like', '%' . $query . '%')
            ->orderBy('title')
            ->limit(20)
            ->get()
            ->map(fn(TaxonomyTerm $taxonomyTerm) => [
                'value' => (string)$taxonomyTerm->id,
                'label' => $taxonomyTerm->title,
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
