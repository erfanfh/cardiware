<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PopupTargetSearchController extends Controller
{
    public function __invoke(Request $request, string $type): JsonResponse
    {
        abort_unless(Gate::allows('popup.create') || Gate::allows('popup.edit'), 403);
        $model = match ($type) {
            'post' => Post::class,
            'page' => Page::class,
            'product' => Product::class,
            default => abort(404),
        };

        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 2) {
            return response()->json(['items' => []]);
        }

        $items = $model::query()
            ->select(['id', 'title'])
            ->where(function ($query) use ($term) {
                $query->where('title', 'like', '%'.$term.'%');
                if (ctype_digit($term)) {
                    $query->orWhere('id', (int) $term);
                }
            })
            ->orderBy('title')
            ->limit(30)
            ->get()
            ->map(fn ($item) => ['value' => (string) $item->id, 'label' => $item->title.' (#'.$item->id.')'])
            ->values();

        return response()->json(['items' => $items]);
    }
}
