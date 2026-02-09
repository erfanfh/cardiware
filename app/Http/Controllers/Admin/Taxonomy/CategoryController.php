<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\BuildTreeAction;
use App\Application\Taxonomy\Actions\BulkDeleteAction;
use App\Application\Taxonomy\Actions\GeneratePathAction;
use App\Application\Taxonomy\Actions\RecordSlugHistoryAction;
use App\Application\Taxonomy\Actions\RegeneratePathAndDescendantsAction;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Taxonomy\StoreCategoryRequest;
use App\Http\Requests\Admin\Taxonomy\UpdateCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
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

    /**
     * Display a listing of categories.
     *
     * @param Request $request
     * @return View
     */
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

        return view('admin.taxonomies.category.index', compact('tree', 'terms', 'editingTerm', 'search'));
    }

    /**
     * Store a newly created category in storage.
     *
     * @param StoreCategoryRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $originalSlug = $request->input('slug');

        if (empty(trim($originalSlug))) {
            $data['slug'] = null; // Sluggable خودش تولید می‌کنه
        }

        $term = TaxonomyTerm::create($data);

        if ($originalSlug && $originalSlug !== $term->slug) {
            return redirect()->route('admin.taxonomies.category.index')
                ->with('success', 'دسته‌بندی ایجاد شد.')
                ->with('warning', "اسلاگ '{$originalSlug}' از قبل وجود داشت. اسلاگ '{$term->slug}' به صورت خودکار تنظیم شد.");
        }

        $generatePathAction = app(GeneratePathAction::class);
        $term->path = $generatePathAction->execute($term);
        $term->saveQuietly();

        return redirect()->route('admin.taxonomies.category.index')
            ->with('success', 'دسته‌بندی ایجاد شد.');
    }

    /**
     * Update the specified category in storage.
     *
     * @param UpdateCategoryRequest $request
     * @param TaxonomyTerm $taxonomy_term
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function update(UpdateCategoryRequest $request, TaxonomyTerm $category): RedirectResponse
    {
        $oldSlug = $category->slug;
        $newSlug = $request->input('slug', $oldSlug);
        $oldParent = $category->parent_id;

        if ($newSlug !== $oldSlug && $request->input('slug_change_confirmed') !== '1') {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['slug' => 'برای تغییر اسلاگ باید از طریق پیام هشدار تأیید کنید.'])
                ->with('slug_change_warning', true);
        }


        $category->update($request->validated());

        //check for sluggable package
        $slugWasChangedBySluggable = ($newSlug && $newSlug !== $category->slug);

        if ($oldSlug !== $category->slug) {
            $this->recordSlugHistoryAction->execute($category, $oldSlug);
        }

        if ($oldSlug !== $category->slug || $oldParent !== $category->parent_id) {
            $this->regeneratePathAction->execute($category);
        }

        $message = 'دسته‌بندی با موفقیت بروزرسانی شد.';

        //show message if slug changes by sluggable
        if ($slugWasChangedBySluggable) {
            return redirect()->route('admin.taxonomies.category.index')
                ->with('success', $message)
                ->with('warning', "اسلاگ '{$newSlug}' از قبل وجود داشت. اسلاگ '{$category->slug}' به صورت خودکار تنظیم شد.");
        }

        return redirect()->route('admin.taxonomies.category.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified category from storage.
     *
     * @param TaxonomyTerm $term
     * @return RedirectResponse
     */
    public function destroy(TaxonomyTerm $term): RedirectResponse
    {
        if ($term->children()->exists()) {
            $term->children()->update(['parent_id' => null]);
        }

        $term->delete();

        return redirect()
            ->route('admin.taxonomies.category.index')
            ->with('success', 'دسته‌بندی حذف شد.');
    }

    /**
     * Handle bulk operations on selected categories.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:taxonomy_terms,id',
        ]);

        $deletedCount = $this->bulkDeleteAction->execute($request->ids);

        return redirect()->route('admin.taxonomies.category.index')
            ->with('success', $deletedCount . ' دسته‌بندی با موفقیت حذف شد.');
    }
}
