<?php

namespace App\Http\Controllers\Front\Players;

use App\Domain\Players\Models\PglImport;
use App\Domain\Players\Models\PglPlayer;
use App\Domain\Players\Models\PglPlayerChange;
use App\Domain\Players\Models\PglTeam;
use Illuminate\View\View;

class PlayersHubController
{
    public function __invoke(\Illuminate\Http\Request $request): View
    {
        $latestImport = PglImport::latest('id')->first();

        $q = trim((string) $request->query('q', ''));
        $searchResults = collect();
        if (mb_strlen($q) >= 3) {
            $searchResults = PglPlayer::primary()
                ->where('name', 'like', "%{$q}%")
                ->with('teams')
                ->orderByDesc('overall')
                ->limit(48)->get();
        }

        return view('front.players.hub', [
            'teams' => PglTeam::orderBy('name')->get(),
            'topPlayers' => PglPlayer::primary()
                ->when($q === '', fn ($query) => $query)
                ->whereNotNull('overall')
                ->with('teams')
                ->orderByDesc('overall')
                ->limit(12)->get(),
            'iranianTeams' => PglTeam::whereIn('id', $this->iranianTeamIds())
                ->orderBy('name')->get(),
            'latestImport' => $latestImport,
            'latestChanges' => $latestImport
                ? PglPlayerChange::where('import_id', $latestImport->id)
                    ->whereIn('type', ['overall', 'transfer'])->orderByDesc('id')->limit(12)->get()
                : collect(),
            'q' => $q,
            'searchResults' => $searchResults,
        ]);
    }

    /** تیم‌های ایرانی شناخته‌شده در کاتالوگ پچ (نام‌ها از Team.bin) */
    private function iranianTeamIds(): array
    {
        return PglTeam::query()
            ->whereIn('name', [
                'PERSEPOLIS FC', 'ESTEGHLAL FC', 'SEPAHAN SC', 'TRACTOR SC', 'FOOLAD FC',
                'ZOB AHAN FC', 'GOL GAHAR FC', 'ALUMINIUM FC', 'NASSAJI MAZANDARAN FC',
                'PAYKAN FC', 'MES RAFSANJAN FC', 'HAVADAR FC', 'MALAVAN FC', 'BAM FC',
            ])->pluck('id')->all();
    }
}
