<?php

namespace App\Http\Controllers\Admin\Video;

use App\Application\Taxonomy\Actions\GetAllTaxonomiesAction;
use App\Application\Video\Actions\SaveVideoAction;
use App\Application\Video\Listings\VideoListing;
use App\Application\Video\Support\VideoEditLock;
use App\Domain\Identity\Models\User;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Video\Enums\VideoSourceTypeEnum;
use App\Domain\Video\Enums\VideoStatusEnum;
use App\Domain\Video\Models\Video;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Video\IndexVideoRequest;
use App\Http\Requests\Admin\Video\SaveVideoRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(IndexVideoRequest $request, VideoListing $listing): View
    {
        Gate::authorize('video.list');

        $filters = $request->validated();
        $videos = $listing
            ->handle(Video::query()->with(['cover', 'author', 'categories', 'tags', 'seoAnalysis', 'editLockUser'])->withCount('comments'), $filters)
            ->paginate($listing->perPage())
            ->withQueryString();

        $selectedAuthorOptions = User::query()
            ->whereIn('id', $filters['author_ids'] ?? [])
            ->get(['id', 'display_name', 'email'])
            ->map(fn(User $user) => ['value' => (string)$user->id, 'label' => $user->display_name . ' (' . $user->email . ')'])
            ->values()
            ->all();

        $selectedCategoryOptions = $this->selectedTaxonomyOptions($filters['category_ids'] ?? []);
        $selectedTagOptions = $this->selectedTaxonomyOptions($filters['tag_ids'] ?? []);

        return view('admin.video.videos.index', [
            'videos' => $videos,
            'statuses' => collect(VideoStatusEnum::cases())->mapWithKeys(fn($v) => [$v->value => $v->label()])->all(),
            'sources' => collect(VideoSourceTypeEnum::cases())->mapWithKeys(fn($v) => [$v->value => $v->label()])->all(),
            'hasActiveFilters' => $listing->hasActiveFilters($filters),
            'selectedAuthorOptions' => $selectedAuthorOptions,
            'selectedCategoryOptions' => $selectedCategoryOptions,
            'selectedTagOptions' => $selectedTagOptions,
            'bulkActionOptions' => DefaultBulkActions::set()->toOptions(),
        ]);
    }

    public function create(GetAllTaxonomiesAction $taxonomies): View
    {
        Gate::authorize('video.create');
        return view('admin.video.videos.create', ['allCategories' => $taxonomies->execute(TaxonomyKeyEnum::VIDEO_CATEGORY)]);
    }

    public function store(SaveVideoRequest $request, SaveVideoAction $action): RedirectResponse
    {
        Gate::authorize('video.create');
        $video = $action->execute(null, $request->validated());
        return redirect()->route('admin.videos.edit', $video)->with('success', 'ویدیو با موفقیت ایجاد شد.');
    }

    public function edit(Request $request, Video $video, GetAllTaxonomiesAction $taxonomies, VideoEditLock $locks): Response
    {
        Gate::authorize('video.edit');
        $previous = $request->session()->get('video_edit_lock');
        $token = $request->old('edit_lock_token');
        if (! is_string($token) && is_array($previous) && ($previous['video_id'] ?? null) === $video->id) {
            $token = $previous['token'] ?? null;
        }
        $editLockToken = $locks->acquire($video, $request->user(), is_string($token) ? $token : null);
        $video->load(['tags', 'categories', 'cover', 'seoMeta']);
        return response()->view('admin.video.videos.edit', [
            'video' => $video,
            'editLockToken' => $editLockToken,
            'allCategories' => $taxonomies->execute(TaxonomyKeyEnum::VIDEO_CATEGORY),
            'selectedCategoryIds' => $video->categories->pluck('id')->all(),
            'selectedTags' => $video->tags->map(fn($tag) => ['id' => $tag->id, 'name' => $tag->title])->all(),
        ])->header('Cache-Control', 'no-store');
    }

    public function update(SaveVideoRequest $request, Video $video, SaveVideoAction $action, VideoEditLock $locks): RedirectResponse
    {
        Gate::authorize('video.edit');
        $token = $request->input('edit_lock_token');
        $video = $locks->save($video, $request->user(), is_string($token) ? $token : '',
            fn () => $action->execute($video, $request->validated()));
        return redirect()->route('admin.videos.edit', $video)
            ->with('video_edit_lock', ['video_id' => $video->id, 'token' => $token])
            ->with('success', 'ویدیو با موفقیت بروزرسانی شد.');
    }

    public function destroy(Video $video, VideoEditLock $locks): RedirectResponse
    {
        Gate::authorize('video.delete');
        $locks->withoutEditor($video, fn () => $video->delete());
        return redirect()->route('admin.videos.index')->with('success', 'ویدیو حذف شد.');
    }

    private function selectedTaxonomyOptions(array $ids): array
    {
        return TaxonomyTerm::query()
            ->whereIn('id', $ids)
            ->get(['id', 'title'])
            ->map(fn(TaxonomyTerm $term) => ['value' => (string)$term->id, 'label' => $term->title])
            ->values()
            ->all();
    }
}
