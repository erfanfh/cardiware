<?php

namespace App\Console\Commands;

use App\Domain\Players\Models\PglImport;
use App\Domain\Players\Models\PglPlayer;
use App\Domain\Players\Models\PglPlayerChange;
use App\Domain\Players\Models\PglTeam;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ایمپورت خروجی pgl6_db_export.py به دیتابیس سایت + ساخت چنج‌لاگ.
 *
 * php artisan pgl:import-players pgl6_export_v2.json --label="آپدیت 6.3"
 */
class ImportPglPlayers extends Command
{
    protected $signature = 'pgl:import-players {file} {--label=} {--source=auto} {--fa-names=}';

    protected $description = 'Import PGL players JSON export (players/teams) and record a changelog';

    public function handle(): int
    {
        ini_set('memory_limit', '1536M');

        $file = $this->argument('file');
        if (!is_file($file)) {
            $this->error("فایل یافت نشد: {$file}");
            return 1;
        }

        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data) || !isset($data['players'])) {
            $this->error('ساختار JSON معتبر نیست (کلاید players یافت نشد).');
            return 1;
        }

        // نام‌های فارسی اختیاری: {"players":{"105950":"مهدی طرمی"},"teams":{"4941":"پرسپولیس"}}
        $faNames = null;
        if ($f = $this->option('fa-names')) {
            if (!is_file($f)) {
                $this->error("فایل نام‌های فارسی یافت نشد: {$f}");
                return 1;
            }
            $faNames = json_decode((string) file_get_contents($f), true);
            if (!is_array($faNames)) {
                $this->error('فایل نام‌های فارسی JSON معتبر نیست.');
                return 1;
            }
        }

        $players = $data['players'];
        $teams = $data['teams'] ?? [];
        $source = $this->option('source') !== 'auto'
            ? $this->option('source')
            : ($data['meta']['overall_from_csv'] ?? false ? 'csv' : 'bin');

        $this->info('بازیکنان: '.count($players).' | تیم‌ها: '.count($teams));

        // فقط ستون‌های لازم برای diff (سبک؛ بدون مدل)
        $existing = \Illuminate\Support\Facades\DB::table('pgl_players')
            ->get(['pid', 'overall', 'pos', 'age', 'height', 'weight', 'team_ids', 'stats'])
            ->keyBy('pid');
        $changesCount = 0;

        DB::transaction(function () use ($players, $teams, $existing, $source, $faNames, &$changesCount) {
            $import = PglImport::create([
                'label' => $this->option('label'),
                'source' => $source,
                'file_sha256' => hash('sha256', (string) file_get_contents($this->argument('file'))),
            ]);

            // تیم‌ها
            foreach (array_chunk($teams, 500, true) as $chunk) {
                $rows = [];
                foreach ($chunk as $tid => $t) {
                    $rows[] = ['id' => (int) $tid, 'name' => $t['name'] ?? "team#{$tid}",
                               'code' => $t['code'] ?? '', 'created_at' => now(), 'updated_at' => now()];
                }
                PglTeam::upsert($rows, ['id'], ['name', 'code', 'updated_at']);
            }

            // کارت‌های چندگانه: برای هر نام، یک نمونه‌ی اصلی (دارای تیم، بیشترین اورال)
            $byName = [];
            foreach ($players as $p) {
                $key = mb_strtolower(trim($p['name'] ?? ''));
                $byName[$key][] = $p;
            }
            $primaryOf = [];
            foreach ($byName as $key => $group) {
                usort($group, function ($a, $b) {
                    $score = fn ($p) => (count($p['team_ids'] ?? []) ? 1000 : 0) + ($p['overall'] ?? 0);
                    return [$score($b), -($b['pid'] ?? 0)] <=> [$score($a), -($a['pid'] ?? 0)];
                });
                $primaryPid = $group[0]['pid'];
                foreach ($group as $p) {
                    $primaryOf[$p['pid']] = $primaryPid;
                }
            }

            // چنج‌لاگ نسبت به وضعیت موجود
            $changeRows = [];
            foreach ($players as $p) {
                $old = $existing->get($p['pid']);
                if (!$old) {
                    continue;
                }
                $entries = $this->diff($old, $p, $teams);
                foreach ($entries as $type => $payload) {
                    $changeRows[] = ['import_id' => $import->id, 'pid' => $p['pid'],
                                     'name' => $p['name'], 'type' => $type,
                                     'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                                     'created_at' => now(), 'updated_at' => now()];
                }
            }
            foreach (array_chunk($changeRows, 500) as $chunk) {
                DB::table('pgl_player_changes')->insert($chunk);
                $changesCount += count($chunk);
            }

            // بازیکنان
            foreach (array_chunk($players, 500) as $chunk) {
                $rows = [];
                foreach ($chunk as $p) {
                    $rows[] = [
                        'pid' => (int) $p['pid'],
                        'import_id' => $import->id,
                        'name' => $p['name'] ?? '',
                        'shirt' => $p['shirt'] ?? '',
                        'height' => $p['height'] ?? null,
                        'weight' => $p['weight'] ?? null,
                        'age' => $p['age'] ?? null,
                        'pos' => $p['pos'] ?? null,
                        'pos_fa' => $p['pos_fa'] ?? '',
                        'overall' => $p['overall'] ?? null,
                        'playing_style' => $p['playing_style'] ?? null,
                        'stats' => json_encode($p['stats'] ?? [], JSON_UNESCAPED_UNICODE),
                        'pos_grades' => json_encode($p['pos_grades'] ?? [], JSON_UNESCAPED_UNICODE),
                        'team_ids' => json_encode($p['team_ids'] ?? [], JSON_UNESCAPED_UNICODE),
                        'is_primary' => ($primaryOf[$p['pid']] ?? $p['pid']) == $p['pid'],
                        'primary_pid' => $primaryOf[$p['pid']] ?? $p['pid'],
                        'created_at' => now(), 'updated_at' => now(),
                    ];
                }
                PglPlayer::upsert($rows, ['pid'], [
                    'import_id', 'name', 'shirt', 'height', 'weight', 'age', 'pos', 'pos_fa',
                    'overall', 'playing_style', 'stats', 'pos_grades', 'team_ids',
                    'is_primary', 'primary_pid', 'updated_at',
                ]);
            }

            // حذف بازیکنانی که در فایل نیستند + رابطه‌های تیمی
            $keepPids = collect($players)->pluck('pid')->all();
            $gone = $existing->keys()->diff($keepPids);
            if ($gone->isNotEmpty()) {
                DB::table('pgl_players')->whereIn('pid', $gone->all())->delete();
            }
            DB::table('pgl_player_team')->truncate();
            $pivot = [];
            foreach ($players as $p) {
                foreach ($p['team_ids'] ?? [] as $tid) {
                    $pivot[] = ['player_pid' => (int) $p['pid'], 'team_id' => (int) $tid];
                }
            }
            foreach (array_chunk($pivot, 1000) as $chunk) {
                DB::table('pgl_player_team')->insert($chunk);
            }

            $import->update([
                'players_count' => count($players),
                'teams_count' => count($teams),
                'changes_count' => $changesCount,
            ]);

            // اعمال نام‌های فارسی (فقط کلیدهای داده‌شده؛ در ایمپورت‌های بعدی هم حفظ می‌شود
            // چون upsert ستون name_fa را بازنویسی نمی‌کند)
            if ($faNames) {
                $faPlayers = $faNames['players'] ?? [];
                foreach ($faPlayers as $pid => $fa) {
                    DB::table('pgl_players')->where('pid', (int) $pid)->update(['name_fa' => (string) $fa]);
                }
                $faTeams = $faNames['teams'] ?? [];
                foreach ($faTeams as $tid => $fa) {
                    DB::table('pgl_teams')->where('id', (int) $tid)->update(['name_fa' => (string) $fa]);
                }
                $this->info('نام‌های فارسی اعمال شد: '.count($faPlayers).' بازیکن، '.count($faTeams).' تیم.');
            }
        });

        $this->info("ایمپورت کامل شد — تغییرات ثبت‌شده: {$changesCount}");
        return 0;
    }

    private function diff(object $old, array $p, array $teams): array
    {
        $out = [];
        $tname = fn ($t) => $teams[(string) $t]['name'] ?? "team#{$t}";
        $oldTeams = json_decode((string) $old->team_ids, true) ?? [];
        $oldStats = json_decode((string) $old->stats, true) ?? [];

        if ($old->overall && ($p['overall'] ?? null) && $old->overall != $p['overall']) {
            $out['overall'] = ['overall' => [$old->overall, $p['overall']]];
        }
        if ($old->pos && ($p['pos'] ?? null) && $old->pos != $p['pos']) {
            $out['position'] = ['position' => [$old->pos, $p['pos']]];
        }
        if ($old->age && ($p['age'] ?? null) && $old->age != $p['age']) {
            $out['age'] = ['age' => [$old->age, $p['age']]];
        }
        if ($old->height && ($p['height'] ?? null) && $old->height != $p['height']) {
            $out['height'] = ['height' => [$old->height, $p['height']]];
        }
        if ($old->weight && ($p['weight'] ?? null) && $old->weight != $p['weight']) {
            $out['weight'] = ['weight' => [$old->weight, $p['weight']]];
        }
        if ($oldTeams != ($p['team_ids'] ?? [])) {
            $out['transfer'] = ['transfer' => [
                'from' => array_map($tname, $oldTeams),
                'to' => array_map($tname, $p['team_ids'] ?? []),
            ]];
        }
        $newStats = $p['stats'] ?? [];
        $statDiff = [];
        foreach ($newStats as $k => $v) {
            if (isset($oldStats[$k]) && $oldStats[$k] != $v) {
                $statDiff[$k] = [$oldStats[$k], $v];
            }
        }
        if ($statDiff) {
            $out['stats'] = ['stats' => $statDiff];
        }
        return $out;
    }
}
