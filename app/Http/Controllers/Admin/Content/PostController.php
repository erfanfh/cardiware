<?php

namespace App\Http\Controllers\Admin\Content;

use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Content\Post\StoreRequest;
use App\Http\Requests\Content\Post\UpdateRequest;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        return view('admin.content.posts.index');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.content.posts.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $post = Post::create($request->validated());

        $post->categories()->sync(
            collect($request->categories)->mapWithKeys(fn($id) => [
                $id => ['type' => TaxonomyTermTypeEnum::CATEGORY->value]
            ])
        );
        $post->syncTags($request->tags);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'نوشته با موفقیت ایجاد شد.');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Post $post
     * @return View
     */
    public function edit(Post $post): View
    {
        return view('admin.content.posts.edit', compact('post'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param Post $post
     * @return RedirectResponse
     */
    public function update(UpdateRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());

        $post->categories()->sync(
            collect($request->categories)->mapWithKeys(fn($id) => [
                $id => ['type' => TaxonomyTermTypeEnum::CATEGORY->value]
            ])
        );
        $post->syncTags($request->tags);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'نوشته با موفقیت بروزرسانی شد.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Post $post
     * @return RedirectResponse
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'نوشته موردنظر با موفقیت حذف شد');
    }

    /**
     * @param Request $request
     * @return View
     */
    public function history(Request $request): View
    {
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
            })->paginate(10);
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
