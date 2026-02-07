<?php

namespace App\Http\Controllers\Admin\Content;

use App\Domain\Content\Models\Post;
use App\Http\Controllers\Controller;
use App\Http\Requests\Content\Post\StoreRequest;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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

        if ($request->is_active) {
            if ($request->is_active == 'true') {
                $posts = $posts->where('is_active', true);
            } else {
                $posts = $posts->where('is_active', false);
            }
        }

        $posts = $posts
            ->with('picture')
            ->paginate(10);

        return view('admin.content.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.content.posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $post = Post::create($request->validated());

        return redirect()->route('admin.posts.edit', $post)->with('success', 'نوشته با موفقیت ایجاد شد.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
