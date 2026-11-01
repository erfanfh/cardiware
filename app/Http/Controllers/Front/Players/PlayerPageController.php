<?php

namespace App\Http\Controllers\Front\Players;

use App\Domain\Players\Models\PglPlayer;
use Illuminate\View\View;

class PlayerPageController
{
    public function __invoke(int $player): View
    {
        $p = PglPlayer::with('teams')->findOrFail($player);

        // کارت‌های چندگانه روی صفحه‌ی نمونه‌ی اصلی جمع می‌شوند
        if (!$p->is_primary && $p->primary_pid && $p->primary_pid != $p->pid) {
            $p = PglPlayer::with('teams')->findOrFail($p->primary_pid);
        }

        return view('front.players.player', [
            'player' => $p,
            'changes' => $p->changes()->with('import')->limit(30)->get(),
        ]);
    }
}
