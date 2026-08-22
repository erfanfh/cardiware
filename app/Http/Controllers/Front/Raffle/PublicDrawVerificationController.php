<?php

namespace App\Http\Controllers\Front\Raffle;

use App\Application\Raffle\Actions\Front\BuildPublicDrawVerificationReportAction;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawRun;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PublicDrawVerificationController extends Controller
{
    public function __invoke(
        string $raffleSlug,
        DrawRun $drawRun,
        BuildPublicDrawVerificationReportAction $report,
    ): JsonResponse {
        $campaign = DrawCampaign::query()->where('slug', $raffleSlug)->firstOrFail();
        abort_unless($campaign->isPubliclyVisible() && (int) $drawRun->campaign_id === (int) $campaign->getKey(), 404);

        return response()
            ->json($report->execute($drawRun), 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ->header('Content-Disposition', 'attachment; filename="raffle-verification-'.$drawRun->public_token.'.json"');
    }
}
