<?php

namespace App\Application\Players\Actions;

use App\Domain\Players\Models\PglImport;
use App\Domain\Players\Models\PglPlayer;
use App\Domain\Players\Models\PglPlayerChange;
use App\Domain\Players\Models\PglTeam;
use Illuminate\Support\Collection;

/**
 * ترکیب تیم + تغییرات آخرین ایمپورت — مشترک بین صفحه‌ی /players/t/{id}
 * و صفحه‌ی تیم لندینگ /{landing}/leagues/{league}/{team}.
 */
class GetPglSquadAction
{
    /**
     * @return array{
     *     team: PglTeam,
     *     squad: Collection<PglPlayer>,
     *     changes: Collection<array-key, Collection<PglPlayerChange>>, // groupBy pid
     *     newPids: array<int, int>,   // pid => 0 (برای isset)
     *     latestImport: ?PglImport,
     * }
     */
    public function execute(int $teamId): array
    {
        $teamModel = PglTeam::findOrFail($teamId);

        $squad = $teamModel->primaryPlayers()
            ->whereNotNull('overall')
            ->orderByDesc('overall')
            ->get();

        $latestImport = PglImport::latest('id')->first();

        $pids = $squad->pluck('pid');

        $changes = $latestImport
            ? PglPlayerChange::where('import_id', $latestImport->id)
                ->whereIn('pid', $pids)
                ->get()->groupBy('pid')
            : collect();

        // بازیکن «جدید» = در ایمپورتِ آخر ساخته شده (created_at حفظ می‌شود؛ import_id برای همه به‌روز می‌شود)
        $newPids = $latestImport
            ? PglPlayer::where('created_at', '>=', $latestImport->created_at)
                ->whereIn('pid', $pids)->pluck('pid')->flip()->all()
            : [];

        return [
            'team' => $teamModel,
            'squad' => $squad,
            'changes' => $changes,
            'newPids' => $newPids,
            'latestImport' => $latestImport,
        ];
    }
}
