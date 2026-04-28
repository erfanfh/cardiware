<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

abstract class BaseTaxonomyListController extends Controller
{
    public function __invoke(
        string            $path,
        ResolveSlugAction $resolveSlugAction,
    ): View|RedirectResponse
    {
        $resolvedSlug = $resolveSlugAction->execute(
            new ResolveSlugDTO(
                scope: $this->slugScope(),
                value: $path,
            )
        );

        if (!$resolvedSlug->found) {
            abort(404);
        }

        if ($resolvedSlug->shouldRedirect && filled($resolvedSlug->redirectTo)) {
            return redirect()->to($resolvedSlug->redirectTo, 301);
        }

        /** @var TaxonomyTerm $term */
        $term = $resolvedSlug->model;

        $posts = $term
            ->posts()
            ->with([
                'picture.primaryVariant',
                'categories'
            ])
            ->paginate(9);

        return view($this->viewName(), [
            'posts' => $posts,
            'term' => $term,
            $this->termViewVariableName() => $term,
        ]);
    }

    abstract protected function slugScope(): SlugScopeEnum;

    protected function viewName(): string
    {
        return 'front.posts.list';
    }

    abstract protected function termViewVariableName(): string;
}
