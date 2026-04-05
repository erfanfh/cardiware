<?php

namespace App\Http\Controllers\Admin\Content;

use App\Application\Content\Actions\CreatePageAction;
use App\Application\Content\Actions\UpdatePageAction;
use App\Application\Content\Listings\PageListing;
use App\Domain\Content\Enums\PageStatusEnum;
use App\Domain\Content\Models\Page;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\IndexPageRequest;
use App\Http\Requests\Admin\Content\UpdatePageRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param IndexPageRequest $request
     * @param PageListing $pageListing
     * @return View
     */
    public function index(IndexPageRequest $request, PageListing $pageListing): View
    {
        Gate::authorize('page.list');

        $pages = $pageListing
            ->handle(Page::with(['author']), $request->validated())
            ->paginate($pageListing->perPage())
            ->withQueryString();

        $statuses = collect(PageStatusEnum::cases())
            ->mapWithKeys(fn(PageStatusEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $hasActiveFilters = $pageListing->hasActiveFilters($request->validated());

        $selectedAuthorIds = collect(request('author_ids', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->values();

        $selectedAuthorOptions = User::query()
            ->whereIn('id', $selectedAuthorIds)
            ->get(['id', 'display_name', 'email'])
            ->map(fn($user) => [
                'value' => (string)$user->id,
                'label' => $user->display_name . ' (' . $user->email . ')',
            ])
            ->values()
            ->all();

        $bulkActionOptions = DefaultBulkActions::set()->toOptions();

        return view('admin.content.pages.index', compact(
            'pages',
            'statuses',
            'hasActiveFilters',
            'bulkActionOptions',
            'selectedAuthorOptions',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        Gate::authorize('page.create');

        return view('admin.content.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UpdatePageRequest $request
     * @param CreatePageAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(UpdatePageRequest $request, CreatePageAction $action): RedirectResponse
    {
        Gate::authorize('page.create');

        $page = $action->execute($request->validated());

        return redirect()->route('admin.pages.edit', $page)->with('success', 'صفحه با موفقیت ایجاد شد.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Page $page
     * @return View
     */
    public function edit(Page $page): View
    {
        Gate::authorize('page.edit');

        return view('admin.content.pages.edit', compact('page'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePageRequest $request
     * @param Page $page
     * @param UpdatePageAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdatePageRequest $request, Page $page, UpdatePageAction $action): RedirectResponse
    {
        Gate::authorize('page.edit');

        $action->execute($page, $request->validated());

        return redirect()->route('admin.pages.edit', $page)->with('success', 'برگه با موفقیت بروزرسانی شد.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Page $page
     * @return RedirectResponse
     */
    public function destroy(Page $page): RedirectResponse
    {
        Gate::authorize('page.delete');

        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'برگه موردنظر با موفقیت حذف شد');
    }
}
