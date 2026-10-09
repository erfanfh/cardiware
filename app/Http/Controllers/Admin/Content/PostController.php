<?php

namespace App\Http\Controllers\Admin\Content;

use App\Application\Content\Actions\CreatePostAction;
use App\Application\Content\Actions\DeletePostAction;
use App\Application\Content\Actions\GetPostFeaturesAction;
use App\Application\Content\Actions\UpdatePostAction;
use App\Application\Content\Listings\PostListing;
use App\Application\Content\Support\PostEditLock;
use App\Application\Taxonomy\Actions\GetAllTaxonomiesAction;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\IndexPostRequest;
use App\Http\Requests\Admin\Content\UpdatePostRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class PostController extends Controller
{
    public function __construct(
        protected GetAllTaxonomiesAction $getAllTaxonomiesAction,
        protected GetPostFeaturesAction  $getPostFeaturesAction,
    )
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexPostRequest $request
     * @param PostListing $postListing
     * @return View
     */
    public function index(IndexPostRequest $request, PostListing $postListing): View
    {
        Gate::authorize('post.list');

        $posts = $postListing
            ->handle(Post::with(['author', 'seoAnalysis', 'editLockUser'])->withCount('comments'), $request->validated())
            ->paginate($postListing->perPage())
            ->withQueryString();

        $statuses = collect(PostStatusEnum::cases())
            ->mapWithKeys(fn(PostStatusEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $hasActiveFilters = $postListing->hasActiveFilters($request->validated());

        $selectedTagIds = collect(request('tag_ids', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->values();

        $selectedTagOptions = TaxonomyTerm::query()
            ->whereIn('id', $selectedTagIds)
            ->get(['id', 'title'])
            ->map(fn($tag) => [
                'value' => (string)$tag->id,
                'label' => $tag->title,
            ])
            ->values()
            ->all();

        $selectedCategoryIds = collect(request('category_ids', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->values();

        $selectedCategoryOptions = TaxonomyTerm::query()
            ->whereIn('id', $selectedCategoryIds)
            ->get(['id', 'title'])
            ->map(fn($tag) => [
                'value' => (string)$tag->id,
                'label' => $tag->title,
            ])
            ->values()
            ->all();

        $selectedAuthorIds = collect(request('author_ids', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->values();

        $selectedAuthorOptions = User::query()
            ->whereIn('id', $selectedAuthorIds)
            ->get(['id', 'display_name', 'email'])
            ->map(fn($user) => [
                'value' => (string)$user->id,
                'label' => $user->display_name . ' (' . $user->email . ')',
            ])
            ->values()
            ->all();

        $bulkActionOptions = DefaultBulkActions::set()->toOptions();

        return view('admin.content.posts.index', compact(
            'posts',
            'statuses',
            'hasActiveFilters',
            'selectedTagOptions',
            'bulkActionOptions',
            'selectedCategoryOptions',
            'selectedAuthorOptions',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        Gate::authorize('post.create');

        return view('admin.content.posts.create', [
            'allCategories' => $this->getAllTaxonomiesAction->execute(TaxonomyKeyEnum::POST_CATEGORY),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UpdatePostRequest $request
     * @param CreatePostAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(UpdatePostRequest $request, CreatePostAction $action): RedirectResponse
    {
        Gate::authorize('post.create');

        $post = $action->execute($request->validated());

        return redirect()->route('admin.posts.edit', $post)
            ->with('success', 'نوشته با موفقیت ایجاد شد.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Post $post
     * @return Response
     */
    public function edit(Request $request, Post $post, PostEditLock $locks): Response
    {
        Gate::authorize('post.edit');

        $previous = $request->session()->get('post_edit_lock');
        $token = $request->old('edit_lock_token');
        if (! is_string($token) && is_array($previous) && ($previous['post_id'] ?? null) === $post->id) {
            $token = $previous['token'] ?? null;
        }
        $editLockToken = $locks->acquire($post, $request->user(), is_string($token) ? $token : null);

        $selectedTags = $post->tags
            ->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->title,
            ])
            ->values()
            ->all();

        $allCategories = $this->getAllTaxonomiesAction->execute(TaxonomyKeyEnum::POST_CATEGORY);
        $selectedCategoryIds = $post->categories()->pluck('taxonomy_terms.id')->all();
        $selectedFeatures = $this->getPostFeaturesAction->execute($post);

        return response()->view('admin.content.posts.edit', compact('post', 'selectedTags', 'allCategories', 'selectedCategoryIds', 'selectedFeatures', 'editLockToken'))
            ->header('Cache-Control', 'no-store');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePostRequest $request
     * @param Post $post
     * @param UpdatePostAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $action, PostEditLock $locks): RedirectResponse
    {
        Gate::authorize('post.edit');

        $token = $request->input('edit_lock_token');
        $locks->save($post, $request->user(), is_string($token) ? $token : '',
            fn () => $action->execute($post, $request->validated()));

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('post_edit_lock', ['post_id' => $post->id, 'token' => $token])
            ->with('success', 'نوشته با موفقیت بروزرسانی شد.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Post $post
     * @param DeletePostAction $action
     * @return RedirectResponse
     */
    public function destroy(Post $post, DeletePostAction $action, PostEditLock $locks): RedirectResponse
    {
        Gate::authorize('post.delete');

        $locks->withoutEditor($post, fn () => $action->execute($post));

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'نوشته موردنظر با موفقیت حذف شد');
    }
}
