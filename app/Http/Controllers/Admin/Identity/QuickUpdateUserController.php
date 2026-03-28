<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Application\Taxonomy\Actions\SyncModelTagsAction;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Project\QuickUpdateProjectRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class QuickUpdateUserController extends Controller
{
    public function __construct(
        protected SyncModelTagsAction $syncModelTagsAction,
        protected AssignSlugAction    $assignSlugAction
    )
    {
    }

    /**
     * @param QuickUpdateProjectRequest $request
     * @param Project $project
     * @return JsonResponse|RedirectResponse
     * @throws Throwable
     */
    public function __invoke(QuickUpdateProjectRequest $request, Project $project): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($project, $data): void {
            $project->update([
                'title' => $data['title'],
                'status' => $data['status'],
                'progress_percent' => $data['progress_percent'],
            ]);

            $desiredSlug = trim((string)(($data['slug'] ?: $data['title']) ?? ''));

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::PROJECT,
                    model: $project,
                    desiredSlug: $desiredSlug,
                )
            );

            $this->syncModelTagsAction->execute($project, [
                'new' => $this->extractTagTitles($data['tags'] ?? null),
            ]);
        });

        $project->load('tags');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'پروژه با موفقیت به‌روزرسانی شد.',
                'project' => [
                    'id' => $project->id,
                    'title' => $project->title,
                    'slug' => $project->slug,
                    'urls' => [
                        'detail' => route('projects.detail', $project),
                        'edit' => route('admin.projects.edit', $project),
                        'destroy' => route('admin.projects.destroy', $project),
                    ],
                    'status' => [
                        'value' => $project->status?->value,
                        'label' => $project->status?->label() ?? 'نامشخص',
                        'color' => $project->status === ProjectStatusEnum::ACTIVE ? 'green' : 'red',
                    ],
                    'progress_percent' => (int)($project->progress_percent ?? 0),
                    'tags' => $project->tags
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
            ->with('success', 'پروژه با موفقیت به‌روزرسانی شد.');
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
