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
        $posts = Post::query();

        if ($request->title)
            $posts = $posts->where('title', 'like', '%' . $request->title . '%');

        if ($request->id)
            $posts = $posts->where('id', $request->id);

        if ($request->author)
            $posts = $posts->whereHas('author', function ($query) use ($request) {
                $query->whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ["%$request->author%"]);
            });

        if ($request->status)
            $posts = $posts->where('status', $request->status);

        $posts = $posts
            ->paginate(10);

        return view('admin.content.posts.index', compact('posts'));
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
}
