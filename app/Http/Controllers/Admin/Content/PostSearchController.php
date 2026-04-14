<?php

namespace App\Http\Controllers\Admin\Content;

use App\Domain\Content\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string)$request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        $items = Post::query()
            ->select(['id', 'title'])
            ->where('title', 'like', '%' . $query . '%')
            ->orderBy('title')
            ->limit(20)
            ->get()
            ->map(fn(Post $post) => [
                'value' => (string)$post->id,
                'label' => $post->title,
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
