<?php

namespace App\Http\Controllers\Front\Vip;

use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Vip\Models\VipPlan;
use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Setting\Groups\VipSettings;
use Illuminate\View\View;

class VipPlanPageController extends Controller
{
    public function __invoke(VipSettings $settings): View
    {
        return view('front.vip.plans', [
            'plans' => app(PublicReadCache::class)->remember(
                'vip-plans', [], [VipPlan::class], fn () => VipPlan::query()->available()->get(),
            ),
            'vipExpiresAt' => auth()->user()?->vipExpiresAt(),
            'hero' => $settings->hero(),
            'featuredPlanId' => $settings->get('featured_plan_id'),
        ]);
    }
}
