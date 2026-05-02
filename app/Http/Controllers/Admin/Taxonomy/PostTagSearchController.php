<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostTagSearchController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        Gate::authorize('tag.manage');

        $query = trim((string)$request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        $items = TaxonomyTerm::query()
            ->where('taxonomy', TaxonomyKeyEnum::POST_TAG)
            ->select(['id', 'title'])
            ->where(function ($builder) use ($query) {
                $builder->where('title', 'like', '%' . $query . '%')
                    ->orWhere('id', $query);
            })
            ->orderBy('title')
            ->limit(50)
            ->get()
            ->map(fn(TaxonomyTerm $taxonomyTerm) => [
                'value' => (string)$taxonomyTerm->id,
                'label' => "$taxonomyTerm->title ($taxonomyTerm->id)",
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
