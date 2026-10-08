<?php

namespace App\Http\Controllers\Front\Career;

use App\Domain\Career\Models\CareerPosition;
use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Setting\Groups\CareersSettings;
use Illuminate\View\View;

class CareerPageController extends Controller
{
    public function __invoke(CareersSettings $settings): View
    {
        $positions = CareerPosition::publiclyAvailable()
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('front.career.index', [
            'careers' => $settings->values(),
            'positions' => $positions,
            'teamCount' => $positions->pluck('department')->filter()->unique()->count(),
            'selectedPositionId' => $positions->contains('id', (int) request('position')) ? (int) request('position') : null,
        ]);
    }
}
