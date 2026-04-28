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

        $posts = Post::query()
            ->with([
                'picture.primaryVariant',
                'categories'
            ])
            ->where('title', 'LIKE', '%' . $request->q . '%');

        $projects = Project::query()
            ->where('title', 'LIKE', '%' . $request->q . '%');

        if ($type === 'posts') {
            $posts = $posts->paginate(9)->withQueryString();
            $postsCount = $posts->total();
            $projectsCount = $projects->count();
        } elseif ($type === 'projects') {
            $projects = $projects->paginate(9)->withQueryString();
            $projectsCount = $projects->total();
            $postsCount = $posts->count();
        } else {
            return redirect()->route('search', ['q' => $request->q]);
        }

        return view('front.search', compact('posts', 'projects', 'type', 'postsCount', 'projectsCount'));
    }
}
