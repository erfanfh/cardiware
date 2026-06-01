<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Application\Catalog\Actions\HandleProductBulkAction;
use App\Http\Requests\Admin\Catalog\BulkProductActionRequest;
use Throwable;

class ProductBulkActionController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        BulkProductActionRequest $request,
        HandleProductBulkAction  $actionHandler
    )
    {
        $result = $actionHandler->execute(
            action: $request->bulkAction(),
            ids: $request->productIds(),
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', $actionHandler->successMessage($result));
    }
}
