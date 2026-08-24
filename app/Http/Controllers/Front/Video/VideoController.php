<?php

namespace App\Http\Controllers\Front\Video;

use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Video\Models\Video;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $this->resolveSort($request);
        $videos = $this->applySort(
            Video::query()->with(['cover', 'categories', 'author'])->withCount('approvedComments'),
            $sort,
        )->paginate(12)->withQueryString();
        $featured = Video::query()->featured()->with(['cover', 'categories'])->latest('published_at')->first();
        $categories = TaxonomyTerm::query()->forTaxonomy(TaxonomyKeyEnum::VIDEO_CATEGORY)
            ->whereNull('parent_id')->withCount('videos')->orderBy('title')->get();
        $trendingVideos = $this->trendingVideos();
        $totalVideos = Video::query()->count();

        request()->attributes->set('seo_paginator', $videos);

        return view('front.videos.index', compact('videos', 'featured', 'categories', 'trendingVideos', 'totalVideos', 'sort'));
    }

    public function show(string $videoSlug, ResolveSlugAction $resolveSlug, StoreViewAction $storeView): View|RedirectResponse
    {
        $resolved = $resolveSlug->execute(new ResolveSlugDTO(SlugScopeEnum::VIDEO, $videoSlug));
        abort_unless($resolved->found && $resolved->model instanceof Video, 404);
        if ($resolved->shouldRedirect) {
            return redirect()->to($resolved->redirectTo, 301);
        }

        /** @var Video $video */
        $video = $resolved->model;
        $video->load(['cover', 'author', 'categories', 'tags', 'seoMeta'])->loadCount('approvedComments');
        $storeView->execute($video);

        $termIds = $video->categories->concat($video->tags)->pluck('id')->unique();
        $relatedVideos = Video::query()->whereKeyNot($video->id)->with(['cover', 'categories'])
            ->when($termIds->isNotEmpty(), fn (Builder $q) => $q->whereHas('taxonomyTerms', fn (Builder $t) => $t->whereIn('taxonomy_terms.id', $termIds)))
            ->latest('published_at')->limit(4)->get();

        request()->attributes->set('video', $video);

        return view('front.videos.detail', compact('video', 'relatedVideos'));
    }

    public function taxonomy(Request $request, string $path, string $taxonomy, ResolveSlugAction $resolveSlug): View|RedirectResponse
    {
        $key = $taxonomy === 'category' ? TaxonomyKeyEnum::VIDEO_CATEGORY : TaxonomyKeyEnum::VIDEO_TAG;
        $resolved = $resolveSlug->execute(new ResolveSlugDTO($key->slugScope(), $path));
        abort_unless($resolved->found && $resolved->model instanceof TaxonomyTerm, 404);
        if ($resolved->shouldRedirect) {
            return redirect()->to($resolved->redirectTo, 301);
        }

        /** @var TaxonomyTerm $term */
        $term = $resolved->model;
        $sort = $this->resolveSort($request);
        $videos = $this->applySort(
            $term->videosIncludingDescendants()->with(['cover', 'categories', 'author'])->withCount('approvedComments'),
            $sort,
        )->paginate(12)->withQueryString();
        request()->attributes->set('video_taxonomy_term', $term);
        request()->attributes->set('seo_paginator', $videos);

        return view('front.videos.index', [
            'videos' => $videos,
            'featured' => null,
            'categories' => TaxonomyTerm::query()->forTaxonomy(TaxonomyKeyEnum::VIDEO_CATEGORY)->whereNull('parent_id')->withCount('videos')->orderBy('title')->get(),
            'term' => $term,
            'trendingVideos' => $this->trendingVideos(),
            'totalVideos' => Video::query()->count(),
            'sort' => $sort,
        ]);
    }

    private function resolveSort(Request $request): string
    {
        return in_array($request->query('sort'), ['latest', 'popular'], true)
            ? $request->query('sort')
            : 'latest';
    }

    private function applySort(Builder $query, string $sort): Builder
    {
        if ($sort !== 'popular') {
            return $query->latest('videos.published_at');
        }

        return $query
            ->leftJoin('page_views', function (JoinClause $join): void {
                $join->on('page_views.pageable_id', '=', 'videos.id')
                    ->where('page_views.pageable_type', Video::class);
            })
            ->select('videos.*')
            ->orderByRaw('COALESCE(page_views.count, 0) DESC')
            ->latest('videos.published_at');
    }

    private function trendingVideos(): Collection
    {
        return $this->applySort(
            Video::query()->with(['cover', 'categories'])->withCount('approvedComments'),
            'popular',
        )->limit(4)->get();
    }
}
