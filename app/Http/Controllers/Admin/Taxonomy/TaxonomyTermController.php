<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Taxonomy\StoreTaxonomyTermRequest;
use App\Http\Requests\Admin\Taxonomy\UpdateTaxonomyTermRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxonomyTermController extends Controller
{
    public function index(Request $request): View
    {
        $editingId = $request->query('edit');
        $search = $request->query('search');

        //search
        $query = TaxonomyTerm::with('children')->orderBy('title');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $terms = $query->get();
        $tree = $this->buildTree($terms->whereNull('parent_id'));

        $editingTerm = $editingId ? TaxonomyTerm::findOrFail($editingId) : null;

        return view('admin.taxonomy.terms.index', compact('tree', 'terms', 'editingTerm', 'search'));
    }

    private function buildTree($items, $level = 0): array
    {
        $result = [];
        foreach ($items as $item) {
            $item->level = $level;
            $result[] = $item;

            if ($item->children->isNotEmpty()) {
                $result = array_merge($result, $this->buildTree($item->children, $level + 1));
            }
        }
        return $result;
    }

    public function create(): View
    {
        $parents = TaxonomyTerm::orderBy('title')->get();
        return view('admin.taxonomy.terms.create', compact('parents'));
    }

    public function store(StoreTaxonomyTermRequest $request): RedirectResponse
    {
        $term = TaxonomyTerm::create($request->validated());

        $term->path = $term->generatePath();
        $term->saveQuietly();

        return redirect()->route('admin.taxonomy.terms.index')
            ->with('success', 'دسته‌بندی ایجاد شد.');
    }

    public function edit(TaxonomyTerm $term): View
    {
        $parents = TaxonomyTerm::where('id', '!=', $term->id)->orderBy('title')->get();
        return view('admin.taxonomy.terms.edit', compact('term', 'parents'));
    }

    public function update(UpdateTaxonomyTermRequest $request, TaxonomyTerm $term): RedirectResponse
    {
        $oldSlug = $term->slug;
        $newSlug = $request->input('slug', $oldSlug);

        if ($newSlug !== $oldSlug && $request->input('slug_change_confirmed') !== '1') {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'slug' => 'برای تغییر اسلاگ باید از طریق پیام هشدار تأیید کنید.'
                ])
                ->with('slug_change_warning', true);
        }

        if ($newSlug !== $oldSlug) {
            $existsInTerms = TaxonomyTerm::where('slug', $newSlug)
                ->where('id', '!=', $term->id)
                ->exists();

            $existsInSlugs = TaxonomyTermSlug::where('route_key', $newSlug)
                ->where('type', $term->type->value)
                ->where('taxonomy_term_id', '!=', $term->id)
                ->exists();

            if ($existsInTerms || $existsInSlugs) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'slug' => 'این اسلاگ قبلاً برای دسته‌بندی دیگری استفاده شده است.'
                    ]);
            }
        }

        $oldParent = $term->parent_id;
        $term->update($request->validated());

        if ($oldSlug !== $term->slug) {
            $term->recordSlugHistory($oldSlug);
        }

        if ($oldSlug !== $term->slug || $oldParent !== $term->parent_id) {
            $term->regeneratePathAndDescendants();
        }

        return redirect()->route('admin.taxonomy.terms.index')
            ->with('success', 'دسته‌بندی با موفقیت بروزرسانی شد.');
    }

    public function destroy(TaxonomyTerm $term): RedirectResponse
    {
        if ($term->children()->exists()) {
            $term->children()->update(['parent_id' => null]);
        }

        $term->delete();

        return redirect()
            ->route('admin.taxonomy.terms.index')
            ->with('success', 'دسته‌بندی حذف شد.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:taxonomy_terms,id',
        ]);

        foreach ($request->ids as $id) {
            $term = TaxonomyTerm::find($id);

            if ($term && $term->children()->exists()) {
                $term->children()->update(['parent_id' => null]);
            }
        }

        $deletedCount = TaxonomyTerm::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.taxonomy.terms.index')
            ->with('success', $deletedCount . ' دسته‌بندی با موفقیت حذف شد.');
    }
}
