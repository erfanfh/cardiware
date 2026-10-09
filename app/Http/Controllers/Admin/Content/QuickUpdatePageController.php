<?php

namespace App\Http\Controllers\Admin\Content;

use App\Application\Content\Support\PageEditLock;
use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Content\Enums\PageStatusEnum;
use App\Domain\Content\Models\Page;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\QuickUpdatePageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class QuickUpdatePageController extends Controller
{
    public function __construct(
        protected AssignSlugAction $assignSlugAction
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(QuickUpdatePageRequest $request, Page $page, PageEditLock $locks): JsonResponse|RedirectResponse
    {
        Gate::authorize('page.edit');

        $data = $request->validated();
        $data['title'] = $data['title'] ?: 'بدون عنوان';
        $data['slug'] = $data['slug'] ?: 'untitled-page';

        $locks->withoutEditor($page, function () use ($page, $data): void {
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
                        'detail' => $page->isPubliclyVisible() ? $page->url : route('preview.pages', $page->id),
                        'edit' => route('admin.pages.edit', $page),
                        'destroy' => route('admin.pages.destroy', $page),
                    ],
                    'detail_label' => $page->isPubliclyVisible() ? 'مشاهده' : 'پیش‌نمایش',
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
