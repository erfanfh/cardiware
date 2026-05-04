<?php

namespace App\Http\Controllers\Front\Search;

use App\Domain\Content\Models\Post;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): RedirectResponse|View
    {
        $type = $request->type ?? 'posts';
        $searchTerm = trim((string)$request->q);

        $postsQuery = Post::query()
            ->with([
                'picture.primaryVariant',
                'picture.variants',
                'categories',
            ])
            ->where('title', 'LIKE', '%' . $searchTerm . '%');

        $projectsQuery = Project::query()
            ->where('title', 'LIKE', '%' . $searchTerm . '%');

        if ($type === 'posts') {
            $posts = $postsQuery->latest()->paginate(12)->withQueryString();

            request()->attributes->set('seo_paginator', $posts);

            $postsCount = $posts->total();
            $projectsCount = $projectsQuery->count();
        } elseif ($type === 'projects') {
            $projects = $projectsQuery->latest()->paginate(9)->withQueryString();

            request()->attributes->set('seo_paginator', $projects);

            $projectsCount = $projects->total();
            $postsCount = $postsQuery->count();
        } else {
            return redirect()->route('search', ['q' => $searchTerm]);
        }

        return view('front.search', compact(
            'posts',
            'projects',
            'type',
            'postsCount',
            'projectsCount'
        ));
    }
}
