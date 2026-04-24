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
        $view = 'admin.layout.page-layouts.home';
        $gate = "page-layout.{$pageLayout->key}";
        switch ($pageLayout->key) {
            case 'home':
                break;
            case 'sidebar':
                $view = 'admin.layout.page-layouts.sidebar';
                break;
            case 'content':
                $view = 'admin.layout.page-layouts.content';
        }
        Gate::authorize($gate);
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
        $gate = "page-layout.{$pageLayout->key}";
        Gate::authorize($gate);

        $updatePageLayoutAction->execute($pageLayout, $request->layoutState(), $request->elementStates());

        return redirect()
            ->route('admin.page-layouts.show', $pageLayout)
            ->with('success', 'چیدمان با موفقیت ذخیره شد.');
    }
}
