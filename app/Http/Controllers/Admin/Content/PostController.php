<?php

namespace App\Http\Controllers\Admin\Content;

use App\Application\Content\Actions\CreatePostAction;
use App\Application\Content\Actions\DeletePostAction;
use App\Application\Content\Actions\GetPostFeaturesAction;
use App\Application\Content\Actions\UpdatePostAction;
use App\Application\Content\Listings\PostListing;
use App\Application\Taxonomy\Actions\GetAllCategoriesAction;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\IndexPostRequest;
use App\Http\Requests\Admin\Content\UpdatePostRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PostController extends Controller
{
    public function __construct(
        protected GetAllCategoriesAction $getAllCategoriesAction,
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
        $posts = $postListing
            ->handle(Post::with(['author']), $request->validated())
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
        return view('admin.content.posts.create', [
            'allCategories' => $this->getAllCategoriesAction->execute(),
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
        $post = $action->execute($request->validated());

        return redirect()->route('admin.posts.edit', $post)
            ->with('success', 'نوشته با موفقیت ایجاد شد.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Post $post
     * @return View
     */
    public function edit(Post $post): View
    {
        $selectedTags = $post->tags
            ->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->title,
            ])
            ->values()
            ->all();

        $allCategories = $this->getAllCategoriesAction->execute();
        $selectedCategoryIds = $post->categories()->pluck('taxonomy_terms.id')->all();
        $selectedFeatures = $this->getPostFeaturesAction->execute($post);

        return view('admin.content.posts.edit', compact('post', 'selectedTags', 'allCategories', 'selectedCategoryIds', 'selectedFeatures'));
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
    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $action): RedirectResponse
    {
        $action->execute($post, $request->validated());

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'نوشته با موفقیت بروزرسانی شد.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Post $post
     * @param DeletePostAction $action
     * @return RedirectResponse
     */
    public function destroy(Post $post, DeletePostAction $action): RedirectResponse
    {
        $action->execute($post);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'نوشته موردنظر با موفقیت حذف شد');
    }

    /**
     * @param Request $request
     * @return View
     */
    public function history(Request $request): View
    {
        $sort = $request->get('sort', 'published_at');
        $direction = $request->get('direction', 'desc');

        $allowedSorts = ['title', 'published_at'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'published_at';
        }

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $posts = Post::query()->withTrashed()->whereNotNull('deleted_at')
            ->when($request->filled('title'), function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->title . '%');
            })->when($request->filled('id'), function ($query) use ($request) {
                $query->where('id', $request->id);
            })->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('id', $request->category);
                });
            })->when($request->filled('tag'), function ($query) use ($request) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->where('id', $request->tag);
                });
            })->when($request->filled('author'), function ($query) use ($request) {
                $query->whereHas('author', function ($q) use ($request) {
                    $q->whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ['%' . $request->author . '%']);
                });
            })->when($request->filled('from_published_at'), function ($query) use ($request) {
                $query->where('created_at', '>=', gregorian_date($request->from_published_at));
            })->when($request->filled('to_published_at'), function ($query) use ($request) {
                $query->where('created_at', '<=', gregorian_date($request->to_published_at));
            })->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.content.posts.history', compact('posts'));
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function restore(int $id): RedirectResponse
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->restore();

        return redirect()->route('admin.posts.history')->with('success', 'نوشته موردنظر با موفقیت بازیابی شد');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->forceDelete();

        return redirect()->route('admin.posts.history')->with('success', 'نوشته موردنظر با موفقیت به وطر کامل حذف شد');
    }
}
