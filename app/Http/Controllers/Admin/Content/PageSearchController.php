<?php

namespace App\Http\Controllers\Admin\Content;

use App\Domain\Content\Models\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PageSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Gate::authorize('page.list');

        $query = trim((string)$request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        $items = Page::query()
            ->select(['id', 'title'])
            ->where(function ($builder) use ($query) {
                $builder->where('title', 'like', '%' . $query . '%')
                    ->orWhere('id', $query);
            })
            ->orderBy('title')
            ->limit(50)
            ->get()
            ->map(fn(Page $page) => [
                'value' => (string)$page->id,
                'label' => "$page->title ($page->id)",
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
