<?php

namespace App\Http\Controllers\Admin\Content;

use App\Domain\Content\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Gate::authorize('post.list');

        $query = trim((string)$request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        $items = Post::query()
            ->select(['id', 'title'])
            ->where(function ($builder) use ($query) {
                $builder->where('title', 'like', '%' . $query . '%')
                    ->orWhere('id', $query);
            })
            ->orderBy('title')
            ->limit(50)
            ->get()
            ->map(fn(Post $post) => [
                'value' => (string)$post->id,
                'label' => "$post->title ($post->id)",
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
