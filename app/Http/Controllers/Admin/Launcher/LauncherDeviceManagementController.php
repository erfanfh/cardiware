<?php

namespace App\Http\Controllers\Admin\Launcher;

use App\Application\Commerce\Actions\Access\AdminRestorePurchaseAccessAction;
use App\Application\Commerce\Actions\Access\AdminRevokePurchaseAccessAction;
use App\Application\Launcher\Actions\Security\ResetLauncherDeviceSecurityAction;
use App\Application\Launcher\Actions\Security\ChangeLauncherAccountSuspensionAction;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class LauncherDeviceManagementController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        // Per-list controls (r_ = repeated resets, w_ = shared-account watchlist,
        // c_ = clone suspects): period, minimum count, sort and how many rows. Each list
        // keeps the original fixed values as its defaults, so the page looks the same
        // until an admin opens a list up.
        $listFilters = [];
        foreach (['r' => [90, 2], 'w' => [30, 2], 'c' => [7, 6]] as $prefix => [$defaultDays, $defaultMin]) {
            $listFilters[$prefix] = [
                'days' => in_array((int)$request->query("{$prefix}_days"), [7, 30, 90, 180, 365], true)
                    ? (int)$request->query("{$prefix}_days") : $defaultDays,
                'min' => max(1, min(1000, (int)($request->query("{$prefix}_min") ?: $defaultMin))),
                'sort' => $request->query("{$prefix}_sort") === 'latest' ? 'latest' : 'count',
                'limit' => in_array((int)$request->query("{$prefix}_limit"), [25, 100, 500], true)
                    ? (int)$request->query("{$prefix}_limit") : 25,
            ];
        }
        $r = $listFilters['r'];
        $w = $listFilters['w'];
        $c = $listFilters['c'];

        $selectedUser = null;
        $selectedUserOption = [];
        $devices = collect();
        $resetLogs = collect();
        $resetStats = null;
        $suspensionLogs = collect();
        $transferBlockLogs = collect();
        $purchaseAccesses = collect();

        // Accounts that keep asking for device resets. One reset is a new laptop; five in a
        // month is an account being passed around. Listed before any search, like the
        // other watchlists, so the pattern is visible without already suspecting someone.
        $resetFrequent = DB::table('launcher_device_reset_logs as logs')
            ->join('users', 'users.id', '=', 'logs.target_user_id')
            ->where('logs.created_at', '>=', now()->subDays($r['days']))
            ->groupBy('logs.target_user_id', 'users.display_name', 'users.first_name', 'users.last_name', 'users.mobile', 'users.email')
            ->havingRaw('COUNT(*) >= ?', [$r['min']])
            ->orderByRaw($r['sort'] === 'latest' ? 'MAX(logs.created_at) DESC' : 'COUNT(*) DESC')
            ->limit($r['limit'] + 1)
            ->get([
                'logs.target_user_id as user_id',
                'users.display_name',
                'users.first_name',
                'users.last_name',
                'users.mobile',
                'users.email',
                DB::raw('COUNT(*) as reset_count'),
                DB::raw('SUM(CASE WHEN logs.created_at >= \'' . now()->subDays(30)->toDateTimeString() . '\' THEN 1 ELSE 0 END) as reset_count_30d'),
                DB::raw('MAX(logs.created_at) as last_reset_at'),
                DB::raw('MIN(logs.created_at) as first_reset_at'),
            ]);

        // Proactive discovery: accounts with 2+ blocked third-device attempts in the last
        // 30 days, most-frequent first, shown even before any user is searched -- so staff
        // can spot likely-shared accounts without already suspecting a specific person.
        $watchlist = DB::table('launcher_device_transfer_blocks as blocks')
            ->join('users', 'users.id', '=', 'blocks.user_id')
            ->where('blocks.created_at', '>=', now()->subDays($w['days']))
            ->groupBy('blocks.user_id', 'users.display_name', 'users.first_name', 'users.last_name', 'users.mobile', 'users.email')
            ->havingRaw('COUNT(*) >= ?', [$w['min']])
            ->orderByRaw($w['sort'] === 'latest' ? 'MAX(blocks.created_at) DESC' : 'COUNT(*) DESC')
            ->limit($w['limit'] + 1)
            ->get([
                'blocks.user_id',
                'users.display_name',
                'users.first_name',
                'users.last_name',
                'users.mobile',
                'users.email',
                DB::raw('COUNT(*) as attempts_count'),
                DB::raw('COUNT(DISTINCT blocks.attempted_public_key_fingerprint) as distinct_devices_count'),
                DB::raw('MAX(blocks.created_at) as last_attempt_at'),
            ]);

        // Devices that keep bouncing between networks faster than one machine plausibly
        // can. A cloned device is invisible to every protocol check -- to the server the
        // two machines ARE the same device -- so behaviour is the only thing left that
        // separates them: a real device settles on one network, while two machines
        // sharing an identity take turns.
        //
        // The filter is on RAPID switches, not on how many networks were seen. A customer
        // who moves house, or whose laptop hands off between wi-fi and a phone hotspot,
        // produces a handful of switches with long gaps; two people playing the same
        // account produce a stream of short ones. Counting distinct networks instead
        // would flag every mobile user in the country.
        $cloneSuspects = DB::table('launcher_device_network_switches as switches')
            ->join('launcher_devices as devices', 'devices.id', '=', 'switches.launcher_device_id')
            ->join('users', 'users.id', '=', 'switches.user_id')
            ->where('switches.created_at', '>=', now()->subDays($c['days']))
            ->where('switches.seconds_since_previous', '<=', 300)
            ->groupBy(
                'switches.launcher_device_id',
                'switches.user_id',
                'devices.name',
                'devices.last_seen_at',
                'users.display_name',
                'users.first_name',
                'users.last_name',
                'users.mobile',
                'users.email'
            )
            ->havingRaw('COUNT(*) >= ?', [$c['min']])
            ->orderByRaw($c['sort'] === 'latest' ? 'MAX(switches.created_at) DESC' : 'COUNT(*) DESC')
            ->limit($c['limit'] + 1)
            ->get([
                'switches.user_id',
                'switches.launcher_device_id',
                'devices.name as device_name',
                'devices.last_seen_at',
                'users.display_name',
                'users.first_name',
                'users.last_name',
                'users.mobile',
                'users.email',
                DB::raw('COUNT(*) as rapid_switch_count'),
                DB::raw('COUNT(DISTINCT switches.network) as distinct_networks_count'),
                DB::raw('MAX(switches.created_at) as last_switch_at'),
            ]);

        if (!empty($validated['user_id'])) {
            $selectedUser = User::query()->findOrFail((int)$validated['user_id']);
            $selectedUserOption = [[
                'value' => (string)$selectedUser->getKey(),
                'label' => $this->userLabel($selectedUser),
            ]];

            $devices = $selectedUser->launcherDevices()
                ->latest('last_seen_at')
                ->latest('id')
                ->get();

            $resetStats = (object) [
                'total' => DB::table('launcher_device_reset_logs')
                    ->where('target_user_id', $selectedUser->getKey())->count(),
                'last_30_days' => DB::table('launcher_device_reset_logs')
                    ->where('target_user_id', $selectedUser->getKey())
                    ->where('created_at', '>=', now()->subDays(30))->count(),
                'last_at' => DB::table('launcher_device_reset_logs')
                    ->where('target_user_id', $selectedUser->getKey())->max('created_at'),
            ];

            $resetLogs = DB::table('launcher_device_reset_logs as logs')
                ->leftJoin('users as admins', 'admins.id', '=', 'logs.admin_user_id')
                ->where('logs.target_user_id', $selectedUser->getKey())
                ->select([
                    'logs.id',
                    'logs.source',
                    'logs.reason',
                    'logs.ip_address',
                    'logs.created_at',
                    'admins.display_name as admin_display_name',
                    'admins.first_name as admin_first_name',
                    'admins.last_name as admin_last_name',
                ])
                ->latest('logs.id')
                ->limit(20)
                ->get();

            $suspensionLogs = DB::table('launcher_access_suspension_logs as logs')
                ->leftJoin('users as admins', 'admins.id', '=', 'logs.admin_user_id')
                ->where('logs.target_user_id', $selectedUser->getKey())
                ->select([
                    'logs.id',
                    'logs.action',
                    'logs.reason',
                    'logs.suspended_until',
                    'logs.ip_address',
                    'logs.created_at',
                    'admins.display_name as admin_display_name',
                    'admins.first_name as admin_first_name',
                    'admins.last_name as admin_last_name',
                ])
                ->latest('logs.id')
                ->limit(20)
                ->get();

            $purchaseAccesses = PurchaseAccess::query()
                ->withoutGlobalScopes()
                ->owned()
                ->with(['product:id,title,slug', 'order:id,status,created_at'])
                ->where('user_id', $selectedUser->getKey())
                ->latest('id')
                ->limit(50)
                ->get();

            $transferBlockLogs = DB::table('launcher_device_transfer_blocks')
                ->where('user_id', $selectedUser->getKey())
                ->latest('id')
                ->limit(20)
                ->get();
        }

        // One extra row was fetched per list only to know whether a "show more" exists.
        $listHasMore = [
            'r' => $resetFrequent->count() > $r['limit'],
            'w' => $watchlist->count() > $w['limit'],
            'c' => $cloneSuspects->count() > $c['limit'],
        ];
        $resetFrequent = $resetFrequent->take($r['limit'])->values();
        $watchlist = $watchlist->take($w['limit'])->values();
        $cloneSuspects = $cloneSuspects->take($c['limit'])->values();

        return view('admin.launcher.devices.index', compact(
            'listFilters',
            'listHasMore',
            'selectedUser',
            'selectedUserOption',
            'devices',
            'resetLogs',
            'resetStats',
            'resetFrequent',
            'suspensionLogs',
            'transferBlockLogs',
            'purchaseAccesses',
            'watchlist',
            'cloneSuspects',
        ));
    }

    public function suspend(
        Request $request,
        ChangeLauncherAccountSuspensionAction $changeSuspension,
    ): RedirectResponse {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'duration_type' => ['required', Rule::in(['permanent', 'days'])],
            'duration_days' => [
                Rule::requiredIf(fn(): bool => $request->input('duration_type') === 'days'),
                'nullable',
                'integer',
                'min:1',
                'max:3650',
            ],
            'suspension_reason' => ['required', 'string', 'min:5', 'max:1000'],
            'confirm_suspension' => ['accepted'],
        ], [
            'duration_days.required' => 'تعداد روزهای محرومیت را وارد کنید.',
            'duration_days.min' => 'مدت محرومیت باید حداقل یک روز باشد.',
            'suspension_reason.required' => 'ثبت علت محرومیت الزامی است.',
            'suspension_reason.min' => 'علت محرومیت باید حداقل ۵ کاراکتر باشد.',
            'confirm_suspension.accepted' => 'برای اعمال محرومیت باید تأیید نهایی را فعال کنید.',
        ]);

        $targetUser = User::query()->findOrFail((int)$validated['user_id']);
        abort_if($request->user()?->is($targetUser), 422, 'مدیر نمی‌تواند حساب لانچر خودش را محروم کند.');

        $changeSuspension->suspend(
            targetUser: $targetUser,
            reason: $validated['suspension_reason'],
            durationDays: $validated['duration_type'] === 'days'
                ? (int)$validated['duration_days']
                : null,
            adminUser: $request->user(),
            ipAddress: $request->ip(),
        );

        return redirect()
            ->route('admin.launcher-devices.index', ['user_id' => $targetUser->getKey()])
            ->with('success', 'دسترسی این حساب به لانچر مسدود شد و Tokenهای لانچر آن باطل شدند.')
            ->with('success_title', 'محرومیت حساب لانچر');
    }

    public function restore(
        Request $request,
        ChangeLauncherAccountSuspensionAction $changeSuspension,
    ): RedirectResponse {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'restore_reason' => ['required', 'string', 'min:5', 'max:1000'],
            'confirm_restore' => ['accepted'],
        ], [
            'restore_reason.required' => 'ثبت دلیل رفع محرومیت الزامی است.',
            'restore_reason.min' => 'دلیل رفع محرومیت باید حداقل ۵ کاراکتر باشد.',
            'confirm_restore.accepted' => 'برای رفع محرومیت باید تأیید نهایی را فعال کنید.',
        ]);

        $targetUser = User::query()->findOrFail((int)$validated['user_id']);
        $changeSuspension->restore(
            targetUser: $targetUser,
            reason: $validated['restore_reason'],
            adminUser: $request->user(),
            ipAddress: $request->ip(),
        );

        return redirect()
            ->route('admin.launcher-devices.index', ['user_id' => $targetUser->getKey()])
            ->with('success', 'محرومیت لانچر رفع شد. کاربر باید یک‌بار به‌صورت آنلاین وارد شود.')
            ->with('success_title', 'رفع محرومیت لانچر');
    }

    /**
     * ابطال دسترسی «یک محصول» کاربر (مکمل مرجوعی جزئی) — بقیه خریدها دست‌نخورده می‌مانند.
     */
    public function revokeAccess(
        Request $request,
        AdminRevokePurchaseAccessAction $revokeAccess,
        int $accessId,
    ): RedirectResponse {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'revoke_reason' => ['required', 'string', 'min:5', 'max:1000'],
            'confirm_revoke' => ['accepted'],
        ], [
            'revoke_reason.required' => 'ثبت دلیل ابطال الزامی است.',
            'revoke_reason.min' => 'دلیل ابطال باید حداقل ۵ کاراکتر باشد.',
            'confirm_revoke.accepted' => 'برای ابطال دسترسی باید تأیید نهایی را فعال کنید.',
        ]);

        $access = PurchaseAccess::query()
            ->withoutGlobalScopes()
            ->owned()
            ->whereKey($accessId)
            ->firstOrFail();

        $revokeAccess->execute($access, $request->user(), $validated['revoke_reason']);

        return redirect()
            ->route('admin.launcher-devices.index', ['user_id' => $access->user_id])
            ->with('success', 'دسترسی این محصول ابطال شد؛ از کتابخانه و لانچر کاربر حذف می‌شود.')
            ->with('success_title', 'ابطال دسترسی محصول');
    }

    /**
     * بازگردانی دسترسی یک محصولِ ابطال‌شده (سفارش باید همچنان پرداخت‌شده باشد).
     */
    public function restoreAccess(
        Request $request,
        AdminRestorePurchaseAccessAction $restoreAccess,
        int $accessId,
    ): RedirectResponse {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'restore_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'restore_reason.required' => 'ثبت دلیل بازگردانی الزامی است.',
            'restore_reason.min' => 'دلیل بازگردانی باید حداقل ۵ کاراکتر باشد.',
        ]);

        $access = PurchaseAccess::query()
            ->withoutGlobalScopes()
            ->owned()
            ->whereKey($accessId)
            ->firstOrFail();

        try {
            $restoreAccess->execute($access, $request->user(), $validated['restore_reason']);
        } catch (ModelNotFoundException) {
            return redirect()
                ->route('admin.launcher-devices.index', ['user_id' => $access->user_id])
                ->with('error', 'سفارش این محصول در وضعیت پرداخت‌شده نیست؛ بازگردانی ممکن نیست.')
                ->with('error_title', 'بازگردانی ناموفق');
        }

        return redirect()
            ->route('admin.launcher-devices.index', ['user_id' => $access->user_id])
            ->with('success', 'دسترسی محصول بازگردانی شد.')
            ->with('success_title', 'بازگردانی دسترسی محصول');
    }

    public function reset(
        Request $request,
        ResetLauncherDeviceSecurityAction $resetDeviceSecurity,
    ): RedirectResponse {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'confirm_reset' => ['accepted'],
        ], [
            'reason.required' => 'ثبت دلیل Reset الزامی است.',
            'reason.min' => 'دلیل Reset باید حداقل ۱۰ کاراکتر باشد.',
            'confirm_reset.accepted' => 'برای انجام Reset باید تأیید نهایی را فعال کنید.',
        ]);

        $targetUser = User::query()->findOrFail((int)$validated['user_id']);
        $revokedDevices = $resetDeviceSecurity->execute(
            targetUser: $targetUser,
            reason: $validated['reason'],
            adminUser: $request->user(),
            source: 'admin',
            ipAddress: $request->ip(),
        );

        return redirect()
            ->route('admin.launcher-devices.index', ['user_id' => $targetUser->getKey()])
            ->with('success', "امنیت دستگاه حساب Reset شد و {$revokedDevices} دستگاه قبلی غیرفعال شد.")
            ->with('success_title', 'Reset دستگاه لانچر');
    }

    private function userLabel(User $user): string
    {
        $displayName = $user->display_name ?: trim($user->first_name . ' ' . $user->last_name);
        $email = $user->email ?: 'بدون ایمیل';

        return "{$displayName} ({$user->mobile} | {$email})";
    }
}
