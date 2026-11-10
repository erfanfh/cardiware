<?php

namespace App\Http\Controllers\Admin\Vip;

use App\Application\System\Actions\UpdateSettingAction;
use App\Domain\Vip\Models\VipPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Vip\UpdateVipPageSettingsRequest;
use App\Infrastructure\Services\Setting\Groups\VipSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use RuntimeException;

class VipPageSettingsController extends Controller
{
    public function edit(VipSettings $settings): View
    {
        Gate::authorize('vip-plan.edit');

        return view('admin.vip.page-settings', [
            'hero' => $settings->hero(),
            'featuredPlanId' => $settings->get('featured_plan_id'),
            'purchaseDiscountPercent' => $settings->purchaseDiscountPercent(),
            'plans' => VipPlan::query()->available()->get(),
        ]);
    }

    public function update(UpdateVipPageSettingsRequest $request, UpdateSettingAction $updateSetting): RedirectResponse
    {
        Gate::authorize('vip-plan.edit');

        DB::transaction(function () use ($request, $updateSetting): void {
            foreach ($request->validated() as $key => $value) {
                if (! $updateSetting->execute('vip', $key, $value)) {
                    throw new RuntimeException('Unable to save VIP page settings.');
                }
            }
        });

        return redirect()->route('admin.vip-plans.page-settings')
            ->with('success', 'تنظیمات صفحه VIP ذخیره شد.');
    }
}
