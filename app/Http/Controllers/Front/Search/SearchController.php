<?php

namespace App\Http\Controllers\Front\Search;

use App\Domain\Content\Models\Post;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::where('title', 'LIKE', '%' . $request->q . '%');
        $postsCount = $posts->count();
        $posts = $posts->paginate(9)->withQueryString();

        $projects = Project::where('title', 'LIKE', '%' . $request->q . '%');
        $projectsCount = $projects->count();
        $projects = $projects->paginate(9)->withQueryString();

        $type = $request->type ?? 'posts';

        return view('front.search', compact('posts', 'projects', 'type', 'postsCount', 'projectsCount'));
    }
}
