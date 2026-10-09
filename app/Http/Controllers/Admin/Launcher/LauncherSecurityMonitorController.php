<?php

namespace App\Http\Controllers\Admin\Launcher;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class LauncherSecurityMonitorController extends Controller
{
    public const TYPE_LABELS = [
        'sider_dll_untrusted' => 'sider.dll ناشناس (غیر از نسخه‌های ما)',
        'launcher_debugger' => 'دیباگر روی لانچر',
        'content_key_pid_mismatch' => 'درخواست کلید از پروسه‌ای غیر از بازی',
        'module_hash_mismatch' => 'ماژول ما دستکاری شده',
        'module_dll_unlisted' => 'DLL ناشناس در پوشه ماژول‌ها',
        'module_lua_unlisted' => 'ماژول Lua خارج از لیست',
        'game_dll_hijack_name' => 'DLL با نام حساس کنار exe بازی',
        'game_dll_unlisted' => 'DLL/ASI دیگر کنار exe بازی',
        'protected_plaintext_present' => 'فایل محافظت‌شده بدون رمز در پوشه بازی (دیتابیس/گرافیک لو رفته یا اشتباه بسته)',
    ];

    public function index(Request $request): View
    {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);
        $since = now()->subDays(30);

        // Accounts ranked by a weighted score: severity 3 counts 10, 2 counts 3, 1 counts 1.
        // Distinct findings are scored, not repeats, so one launch loop cannot outrank a
        // user with three different red flags.
        $riskyUsers = DB::table('launcher_security_events as e')
            ->join('users', 'users.id', '=', 'e.user_id')
            ->where('e.last_seen_at', '>=', $since)
            ->groupBy('e.user_id', 'users.display_name', 'users.first_name', 'users.last_name', 'users.mobile', 'users.email')
            ->orderByRaw('score DESC')
            ->limit(30)
            ->get([
                'e.user_id',
                'users.display_name', 'users.first_name', 'users.last_name', 'users.mobile', 'users.email',
                DB::raw('SUM(CASE e.severity WHEN 3 THEN 10 WHEN 2 THEN 3 ELSE 1 END) as score'),
                DB::raw('SUM(CASE WHEN e.severity = 3 THEN 1 ELSE 0 END) as high_count'),
                DB::raw('COUNT(*) as finding_count'),
                DB::raw('MAX(e.last_seen_at) as last_seen_at'),
            ]);

        $allowedDlls = DB::table('launcher_allowed_dlls')->orderByDesc('id')->get();
        $allowedSet = $allowedDlls->pluck('sha256')->flip();

        // Files whose exact bytes appear on only one or two accounts. A popular ReShade
        // dinput8.dll shows up on hundreds of machines; a hand-built hook shows up on one.
        $rareHashes = DB::table('launcher_security_events')
            ->whereNotNull('sha256')
            ->where('last_seen_at', '>=', $since)
            ->whereIn('event_type', ['sider_dll_untrusted', 'module_dll_unlisted', 'module_hash_mismatch',
                'game_dll_hijack_name', 'game_dll_unlisted'])
            ->groupBy('event_type', 'sha256')
            ->havingRaw('COUNT(DISTINCT user_id) <= 2')
            ->orderByRaw('MAX(severity) DESC, MAX(last_seen_at) DESC')
            ->limit(40)
            ->get([
                'event_type', 'sha256',
                DB::raw('MAX(subject) as subject'),
                DB::raw('MAX(severity) as severity'),
                DB::raw('COUNT(DISTINCT user_id) as user_count'),
                DB::raw('MIN(user_id) as sample_user_id'),
                DB::raw('MAX(last_seen_at) as last_seen_at'),
            ])
            ->reject(fn ($row) => isset($allowedSet[$row->sha256]))
            ->values();

        // Every not-yet-allowed DLL seen next to the game exe, most common first -- the
        // source for the allowlist. A hash on dozens of accounts is a public tool
        // (ReShade, the patch's hook loader): approve those, then switch enforcement on.
        $seenGameDlls = DB::table('launcher_security_events')
            ->whereIn('event_type', ['game_dll_hijack_name', 'game_dll_unlisted'])
            ->whereNotNull('sha256')
            ->where('last_seen_at', '>=', now()->subDays(90))
            ->groupBy('sha256')
            ->orderByRaw('COUNT(DISTINCT user_id) DESC')
            ->limit(80)
            ->get([
                'sha256',
                DB::raw('MAX(subject) as subject'),
                DB::raw('COUNT(DISTINCT user_id) as user_count'),
                DB::raw('MIN(user_id) as sample_user_id'),
                DB::raw('MAX(last_seen_at) as last_seen_at'),
            ])
            ->reject(fn ($row) => isset($allowedSet[$row->sha256]))
            ->values();

        // Devices using the API recently, on a launcher version known to report (at least
        // one device of that version has reported), that have not reported themselves in
        // 7 days -- someone may be blocking that one request.
        $reportingVersions = DB::table('launcher_devices')
            ->whereNotNull('last_security_report_at')
            ->whereNotNull('app_version')
            ->distinct()
            ->pluck('app_version');

        $silentDevices = $reportingVersions->isEmpty()
            ? collect()
            : DB::table('launcher_devices as d')
                ->join('users', 'users.id', '=', 'd.user_id')
                ->whereNull('d.revoked_at')
                ->where('d.last_seen_at', '>=', now()->subDays(3))
                ->whereIn('d.app_version', $reportingVersions)
                ->where(fn ($q) => $q->whereNull('d.last_security_report_at')
                    ->orWhere('d.last_security_report_at', '<', now()->subDays(7)))
                ->orderByDesc('d.last_seen_at')
                ->limit(30)
                ->get([
                    'd.user_id', 'd.name as device_name', 'd.app_version', 'd.last_seen_at', 'd.last_security_report_at',
                    'users.display_name', 'users.first_name', 'users.last_name', 'users.mobile', 'users.email',
                ]);

        $selectedUser = null;
        $userEvents = collect();
        if (!empty($validated['user_id'])) {
            $selectedUser = DB::table('users')->where('id', $validated['user_id'])
                ->first(['id as user_id', 'display_name', 'first_name', 'last_name', 'mobile', 'email']);
            $userEvents = DB::table('launcher_security_events as e')
                ->leftJoin('launcher_devices as d', 'd.id', '=', 'e.launcher_device_id')
                ->where('e.user_id', $validated['user_id'])
                ->orderByDesc('e.severity')
                ->orderByDesc('e.last_seen_at')
                ->limit(200)
                ->get(['e.*', 'd.name as device_name']);
        }

        return view('admin.launcher.security.index', [
            'riskyUsers' => $riskyUsers,
            'rareHashes' => $rareHashes,
            'seenGameDlls' => $seenGameDlls,
            'allowedDlls' => $allowedDlls,
            'silentDevices' => $silentDevices,
            'selectedUser' => $selectedUser,
            'userEvents' => $userEvents,
            'typeLabels' => self::TYPE_LABELS,
        ]);
    }

    // Layer 2: a code copied out of a leaked file (hex editor, at a watermark site) ->
    // the account it was issued to.
    public function watermarkLookup(Request $request,
        \App\Application\Launcher\Actions\Security\LauncherWatermarkCodes $codes): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate(['code' => ['required', 'string', 'max:64']]);
        $userId = $codes->findUserId($validated['code']);
        if ($userId === null) {
            return back()->withInput()->with('error', 'این کد به هیچ حسابی تعلق ندارد (۱۶ رقم هگز را کامل وارد کنید).');
        }

        return redirect()->route('admin.launcher-security.index', ['user_id' => $userId])
            ->with('success', 'کد واترمارک متعلق به همین حساب است.');
    }

    public function allowDll(Request $request): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'sha256' => ['required', 'regex:/\A[a-fA-F0-9]{64}\z/'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::table('launcher_allowed_dlls')->updateOrInsert(
            ['sha256' => strtolower($validated['sha256'])],
            [
                'file_name' => $validated['file_name'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by' => $request->user()?->getKey(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
        Cache::forget('launcher_allowed_dlls');

        return back()->with('success', 'DLL به لیست مجاز اضافه شد.');
    }

    public function revokeDll(int $allowedDll): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');

        DB::table('launcher_allowed_dlls')->where('id', $allowedDll)->delete();
        Cache::forget('launcher_allowed_dlls');

        return back()->with('success', 'DLL از لیست مجاز حذف شد.');
    }
}
