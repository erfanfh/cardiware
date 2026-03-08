<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\BulkDeleteAction;
use App\Application\Taxonomy\Actions\GeneratePathAction;
use App\Application\Taxonomy\Actions\RecordSlugHistoryAction;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Taxonomy\StoreTagRequest;
use App\Http\Requests\Admin\Taxonomy\UpdateTagRequest;
use App\Infrastructure\Traits\Searchable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TagController extends Controller
{
    use Searchable;

    protected RecordSlugHistoryAction $recordSlugHistoryAction;
    protected BulkDeleteAction $bulkDeleteAction;

    public function __construct(
        RecordSlugHistoryAction $recordSlugHistoryAction,
        BulkDeleteAction        $bulkDeleteAction
    )
    {
        $this->recordSlugHistoryAction = $recordSlugHistoryAction;
        $this->bulkDeleteAction = $bulkDeleteAction;
    }

    /**
     * Display a listing of tags.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $editingId = $request->query('edit');
        $search = $request->query('search');

        $query = TaxonomyTerm::tag()->orderBy('title');

        $query = $this->applySearch($query, $search, [
            'title',
            'slug',
            'description',
        ]);

        $tags = $query->get();

        $editingTerm = $editingId ? TaxonomyTerm::findOrFail($editingId) : null;

        return view('admin.taxonomies.tags.index', compact('tags', 'editingTerm', 'search'));
    }

    /**
     * Store a newly created tag in storage.
     *
     * @param StoreTagRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTagRequest $request)
    {
        $data = $request->validated();
        $originalSlug = $request->input('slug');

        // Set type to tag
        $data['type'] = TaxonomyTermTypeEnum::TAG->value;

        // If slug is empty, let Sluggable generate it
        if (empty(trim($originalSlug))) {
            $data['slug'] = null;
        }

        $tag = TaxonomyTerm::create($data);

        //Check if slug was changed by Sluggable
        if ($originalSlug && $originalSlug !== $tag->slug) {
            return redirect()->route('admin.tags.index')
                ->with('success', 'برچسب ایجاد شد.')
                ->with('warning', "اسلاگ '{$originalSlug}' از قبل وجود داشت. اسلاگ '{$tag->slug}' به صورت خودکار تنظیم شد.");
        }

        $generatePathAction = app(GeneratePathAction::class);
        $tag->path = $generatePathAction->execute($tag);
        $tag->saveQuietly();

        return redirect()->route('admin.tags.index')
            ->with('success', 'برچسب ایجاد شد.');
    }

    /**
     * Update the specified tag in storage.
     *
     * @param UpdateTagRequest $request
     * @param TaxonomyTerm $tag
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function update(UpdateTagRequest $request, TaxonomyTerm $tag)
    {
        $oldSlug = $tag->slug;
        $newSlug = $request->input('slug', $oldSlug);

        // Check for slug change without confirmation
        if ($newSlug !== $oldSlug && $request->input('slug_change_confirmed') !== '1') {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['slug' => 'برای تغییر اسلاگ باید از طریق پیام هشدار تأیید کنید.'])
                ->with('slug_change_warning', true);
        }

        $tag->update($request->validated());

        //Check if Sluggable changed the slug
        $slugWasChangedBySluggable = ($newSlug && $newSlug !== $tag->slug);

        // Record slug history if changed
        if ($oldSlug !== $tag->slug) {
            $this->recordSlugHistoryAction->execute($tag, $oldSlug);
        }

        $message = 'برچسب با موفقیت بروزرسانی شد.';

        if ($slugWasChangedBySluggable) {
            return redirect()->route('admin.tags.index')
                ->with('success', $message)
                ->with('warning', "اسلاگ '{$newSlug}' از قبل وجود داشت. اسلاگ '{$tag->slug}' به صورت خودکار تنظیم شد.");
        }

        return redirect()->route('admin.tags.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified tag from storage.
     *
     * @param TaxonomyTerm $tag
     * @return RedirectResponse
     */
    public function destroy(TaxonomyTerm $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'برچسب حذف شد.');
    }

    /**
     * Handle bulk operations on tags.
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

        return redirect()->route('admin.tags.index')
            ->with('success', $deletedCount . ' برچسب با موفقیت حذف شد.');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function tagsFetch(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        $tags = TaxonomyTerm::tag()
            ->where('title', 'like', "%{$query}%")
            ->get(['id', 'title']);

        return response()->json($tags);
    }
}
