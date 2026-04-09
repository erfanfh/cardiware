<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;

class CategoryListController extends Controller
{
    public function __invoke(string $path)
    {
        $category = TaxonomyTerm::categories()->where('path', $path)->firstOrFail();

        $posts = $category->posts()->paginate(9);

        return view('front.posts.list', compact('posts', 'category'));
    }
}
