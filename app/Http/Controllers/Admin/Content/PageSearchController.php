<?php

namespace App\Http\Controllers\Admin\Content;

use App\Domain\Content\Models\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string)$request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        $items = Page::query()
            ->select(['id', 'title'])
            ->where('title', 'like', '%' . $query . '%')
            ->orderBy('title')
            ->limit(20)
            ->get()
            ->map(fn(Page $page) => [
                'value' => (string)$page->id,
                'label' => $page->title,
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
