<?php

namespace App\Http\Controllers\Admin\Content;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Content\Enums\PageStatusEnum;
use App\Domain\Content\Models\Page;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\QuickUpdatePageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class QuickUpdatePageController extends Controller
{
    public function __construct(
        protected AssignSlugAction $assignSlugAction
    )
    {
    }

    /**
     * @param QuickUpdatePageRequest $request
     * @param Page $page
     * @return JsonResponse|RedirectResponse
     * @throws Throwable
     */
    public function __invoke(QuickUpdatePageRequest $request, Page $page): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($page, $data): void {
            $page->update([
                'title' => $data['title'],
                'status' => $data['status'],
            ]);

            $desiredSlug = first_filled($data['slug'] ?? null, $data['title'] ?? null);

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::CONTENT,
                    model: $page,
                    desiredSlug: $desiredSlug,
                )
            );
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'برگه با موفقیت به‌روزرسانی شد.',
                'updatedItem' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'urls' => [
                        'detail' => route('contents.detail', $page->slug),
                        'edit' => route('admin.pages.edit', $page),
                        'destroy' => route('admin.pages.destroy', $page),
                    ],
                    'status' => [
                        'value' => $page->status?->value,
                        'label' => $page->status?->label() ?? 'نامشخص',
                        'color' => $page->status === PageStatusEnum::ACTIVE ? 'green' : 'red',
                    ],
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'برگه با موفقیت به‌روزرسانی شد.');
    }
}
