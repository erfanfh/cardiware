<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;

class TagListController extends Controller
{
    public function __invoke(string $path)
    {
        $tag = TaxonomyTerm::tags()->where('path', $path)->firstOrFail();
        return '';
    }
}
