<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Application\Loyalty\Actions\EarningRule\HandleEarningRuleBulkAction;
use App\Http\Requests\Admin\Loyalty\BulkEarningRuleActionRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class EarningRuleBulkActionController
{
    /** @throws Throwable */
    public function __invoke(
        BulkEarningRuleActionRequest $request,
        HandleEarningRuleBulkAction $handler,
    ): RedirectResponse {
        $result = $handler->execute(
            action: $request->bulkAction(),
            ids: $request->earningRuleIds(),
        );

        return redirect()
            ->route('admin.earning-rules.index')
            ->with('success', $handler->successMessage($result));
    }
}
