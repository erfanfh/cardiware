<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Application\Marketplace\Actions\HandleStoreBulkAction;
use App\Http\Requests\Admin\Marketplace\BulkStoreActionRequest;
use Throwable;

class StoreBulkActionController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        BulkStoreActionRequest $request,
        HandleStoreBulkAction  $actionHandler
    )
    {
        $result = $actionHandler->execute(
            action: $request->bulkAction(),
            ids: $request->storeIds(),
        );

        return redirect()
            ->route('admin.stores.index')
            ->with('success', $actionHandler->successMessage($result));
    }
}
