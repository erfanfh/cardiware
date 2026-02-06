<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\BuildTreeAction;
use App\Application\Taxonomy\Actions\BulkDeleteAction;
use App\Application\Taxonomy\Actions\GeneratePathAction;
use App\Application\Taxonomy\Actions\RecordSlugHistoryAction;
use App\Application\Taxonomy\Actions\RegeneratePathAndDescendantsAction;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Taxonomy\StoreTaxonomyTermRequest;
use App\Http\Requests\Admin\Taxonomy\UpdateTaxonomyTermRequest;
use App\Infrastructure\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxonomyTermController extends Controller
{
    use Searchable;
    protected RecordSlugHistoryAction $recordSlugHistoryAction;
    protected RegeneratePathAndDescendantsAction $regeneratePathAction;
    protected BuildTreeAction $buildTreeAction;
    protected BulkDeleteAction $bulkDeleteAction;

    public function __construct(
        RecordSlugHistoryAction            $recordSlugHistoryAction,
        RegeneratePathAndDescendantsAction $regeneratePathAction,
        BuildTreeAction                    $buildTreeAction,
        BulkDeleteAction                   $bulkDeleteAction
    )
    {
        $this->recordSlugHistoryAction = $recordSlugHistoryAction;
        $this->regeneratePathAction = $regeneratePathAction;
        $this->buildTreeAction = $buildTreeAction;
        $this->bulkDeleteAction = $bulkDeleteAction;
    }

    public function index(Request $request): View
    {
        $editingId = $request->query('edit');
        $search = $request->query('search');

        $query = TaxonomyTerm::with('children')->orderBy('title');

        $query = $this->applySearch($query, $search, [
            'title',
            'slug',
            'description',
        ]);

        $terms = $query->get();
        $tree = $this->buildTreeAction->execute($terms->whereNull('parent_id'));


        $editingTerm = $editingId ? TaxonomyTerm::findOrFail($editingId) : null;

        return view('admin.taxonomy_terms.index', compact('tree', 'terms', 'editingTerm', 'search'));
    }

    public function create(): View
    {
        $parents = TaxonomyTerm::orderBy('title')->get();
        return view('admin.taxonomy_terms.create', compact('parents'));
    }

    public function store(StoreTaxonomyTermRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $originalSlug = $request->input('slug');

        if (empty(trim($originalSlug))) {
            $data['slug'] = null; // Sluggable خودش تولید می‌کنه
        }

        $term = TaxonomyTerm::create($data);

        if ($originalSlug && $originalSlug !== $term->slug) {
            return redirect()->route('admin.taxonomy_terms.index')
                ->with('success', 'دسته‌بندی ایجاد شد.')
                ->with('warning', "اسلاگ '{$originalSlug}' از قبل وجود داشت. اسلاگ '{$term->slug}' به صورت خودکار تنظیم شد.");
        }

        $generatePathAction = app(GeneratePathAction::class);
        $term->path = $generatePathAction->execute($term);
        $term->saveQuietly();

        return redirect()->route('admin.taxonomy_terms.index')
            ->with('success', 'دسته‌بندی ایجاد شد.');
    }

    public function edit(TaxonomyTerm $term): View
    {
        $parents = TaxonomyTerm::where('id', '!=', $term->id)->orderBy('title')->get();
        return view('admin.taxonomy_terms.edit', compact('term', 'parents'));
    }

    public function update(UpdateTaxonomyTermRequest $request, TaxonomyTerm $taxonomy_term): RedirectResponse
    {
        $oldSlug = $taxonomy_term->slug;
        $newSlug = $request->input('slug', $oldSlug);
        $oldParent = $taxonomy_term->parent_id;

        if ($newSlug !== $oldSlug && $request->input('slug_change_confirmed') !== '1') {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['slug' => 'برای تغییر اسلاگ باید از طریق پیام هشدار تأیید کنید.'])
                ->with('slug_change_warning', true);
        }


        $taxonomy_term->update($request->validated());

        //check for sluggable package
        $slugWasChangedBySluggable = ($newSlug && $newSlug !== $taxonomy_term->slug);

        if ($oldSlug !== $taxonomy_term->slug) {
            $this->recordSlugHistoryAction->execute($taxonomy_term, $oldSlug);
        }

        if ($oldSlug !== $taxonomy_term->slug || $oldParent !== $taxonomy_term->parent_id) {
            $this->regeneratePathAction->execute($taxonomy_term);
        }

        $message = 'دسته‌بندی با موفقیت بروزرسانی شد.';

        //show message if slug changes by sluggable
        if ($slugWasChangedBySluggable) {
            return redirect()->route('admin.taxonomy_terms.index')
                ->with('success', $message)
                ->with('warning', "اسلاگ '{$newSlug}' از قبل وجود داشت. اسلاگ '{$taxonomy_term->slug}' به صورت خودکار تنظیم شد.");
        }

        return redirect()->route('admin.taxonomy_terms.index')
            ->with('success', $message);
    }

    public function destroy(TaxonomyTerm $term): RedirectResponse
    {
        if ($term->children()->exists()) {
            $term->children()->update(['parent_id' => null]);
        }

        $term->delete();

        return redirect()
            ->route('admin.taxonomy_terms.index')
            ->with('success', 'دسته‌بندی حذف شد.');
    }

    //Handle bulk operations (delete) on selected taxonomy terms.
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:taxonomy_terms,id',
        ]);

        $deletedCount = $this->bulkDeleteAction->execute($request->ids);

        return redirect()->route('admin.taxonomy_terms.index')
            ->with('success', $deletedCount . ' دسته‌بندی با موفقیت حذف شد.');
    }
}
