<?php

namespace App\Http\Controllers\Admin\Content;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Taxonomy\Actions\SyncModelPostTagsAction;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\QuickUpdatePostRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class QuickUpdatePostController extends Controller
{
    public function __construct(
        protected SyncModelPostTagsAction $syncModelPostTagsAction,
        protected AssignSlugAction        $assignSlugAction
    )
    {
    }

    /**
     * @param QuickUpdatePostRequest $request
     * @param Post $post
     * @return JsonResponse|RedirectResponse
     * @throws Throwable
     */
    public function __invoke(QuickUpdatePostRequest $request, Post $post): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($post, $data): void {
            $post->update([
                'title' => $data['title'],
                'status' => $data['status'],
            ]);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::CONTENT,
                    model: $post,
                    desiredSlug: $desiredSlug,
                )
            );

            $this->syncModelPostTagsAction->execute($post, [
                'new' => $this->extractTagTitles($data['tags'] ?? null),
            ]);
        });

        $post->load('tags');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'نوشته با موفقیت به‌روزرسانی شد.',
                'updatedItem' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'urls' => [
                        'detail' => route('contents.detail', $post->slug),
                        'edit' => route('admin.posts.edit', $post),
                        'destroy' => route('admin.posts.destroy', $post),
                    ],
                    'status' => [
                        'value' => $post->status?->value,
                        'label' => $post->status?->label() ?? 'نامشخص',
                        'color' => $post->status === PostStatusEnum::ACTIVE ? 'green' : 'red',
                    ],
                    'tags' => $post->tags
                        ->map(fn($tag) => [
                            'id' => $tag->id,
                            'title' => $tag->title,
                        ])
                        ->values()
                        ->all(),
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'نوشته با موفقیت به‌روزرسانی شد.');
    }

    /**
     * @param string|null $value
     * @return array<int, string>
     */
    protected function extractTagTitles(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return collect(preg_split('/[،,]+/u', $value))
            ->map(fn($item) => preg_replace('/\s+/', ' ', trim((string)$item)))
            ->filter()
            ->unique(fn(string $item) => mb_strtolower($item))
            ->values()
            ->all();
    }
}
