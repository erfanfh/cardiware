<?php

namespace App\Http\Controllers\Admin\Vip;

use App\Application\Vip\Actions\HandleVipPlanBulkAction;
use App\Http\Requests\Admin\Vip\BulkVipPlanActionRequest;
use Illuminate\Http\RedirectResponse;

class VipPlanBulkActionController
{
    public function __invoke(BulkVipPlanActionRequest $request, HandleVipPlanBulkAction $action): RedirectResponse
    {
        $result = $action->execute($request->bulkAction(), $request->planIds());

        $message = match ($result->action) {
            'activate' => "{$result->affectedCount} پلن VIP فعال شد.",
            'deactivate' => "{$result->affectedCount} پلن VIP غیرفعال شد.",
            'delete' => "{$result->affectedCount} پلن VIP حذف شد.",
        };

        return redirect()->route('admin.vip-plans.index')->with('success', $message);
    }
}
