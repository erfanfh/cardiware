<?php

namespace App\Http\Controllers\Admin\Layout;

use App\Application\Layout\Actions\GetPageLayoutBuilderDataAction;
use App\Application\Layout\Actions\UpdatePageLayoutAction;
use App\Domain\Layout\Models\PageLayout;
use App\Http\Requests\Admin\Layout\UpdatePageLayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class PageLayoutController
{
    /**
     * @param PageLayout $pageLayout
     * @param GetPageLayoutBuilderDataAction $getPageLayoutBuilderDataAction
     * @return View
     */
    public function show(PageLayout $pageLayout, GetPageLayoutBuilderDataAction $getPageLayoutBuilderDataAction): View
    {
        Gate::authorize("page-layout.{$pageLayout->key}");

        $view = '';
        switch ($pageLayout->key) {
            case 'home':
                $view = 'admin.layout.page-layouts.home';
                break;
            case 'sidebar':
                $view = 'admin.layout.page-layouts.sidebar';
                break;
            case 'post-content':
                $view = 'admin.layout.page-layouts.post-content';
        }
        $builderData = $getPageLayoutBuilderDataAction->execute($pageLayout);

        return view($view, [
            'layout' => $builderData['layout'],
            'layoutRows' => $builderData['rows'],
        ]);
    }

    /**
     * @param PageLayout $pageLayout
     * @param UpdatePageLayoutRequest $request
     * @param UpdatePageLayoutAction $updatePageLayoutAction
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(PageLayout              $pageLayout,
                           UpdatePageLayoutRequest $request,
                           UpdatePageLayoutAction  $updatePageLayoutAction,
    ): RedirectResponse
    {
        Gate::authorize("page-layout.{$pageLayout->key}");

        $updatePageLayoutAction->execute($pageLayout, $request->layoutState(), $request->elementStates());

        return redirect()
            ->route('admin.page-layouts.show', $pageLayout)
            ->with('success', 'چیدمان با موفقیت ذخیره شد.');
    }
}
