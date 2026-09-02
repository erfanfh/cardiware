<?php

namespace App\Http\Controllers\Admin\Launcher;

use App\Application\Launcher\Actions\Security\ResetLauncherDeviceSecurityAction;
use App\Application\Launcher\Actions\Security\ChangeLauncherAccountSuspensionAction;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
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

        $selectedUser = null;
        $selectedUserOption = [];
        $devices = collect();
        $resetLogs = collect();
        $suspensionLogs = collect();
        $transferBlockLogs = collect();

        // Proactive discovery: accounts with 2+ blocked third-device attempts in the last
        // 30 days, most-frequent first, shown even before any user is searched -- so staff
        // can spot likely-shared accounts without already suspecting a specific person.
        $watchlist = DB::table('launcher_device_transfer_blocks as blocks')
            ->join('users', 'users.id', '=', 'blocks.user_id')
            ->where('blocks.created_at', '>=', now()->subDays(30))
            ->groupBy('blocks.user_id', 'users.display_name', 'users.first_name', 'users.last_name', 'users.mobile', 'users.email')
            ->havingRaw('COUNT(*) >= 2')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(25)
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

            $transferBlockLogs = DB::table('launcher_device_transfer_blocks')
                ->where('user_id', $selectedUser->getKey())
                ->latest('id')
                ->limit(20)
                ->get();
        }

        return view('admin.launcher.devices.index', compact(
            'selectedUser',
            'selectedUserOption',
            'devices',
            'resetLogs',
            'suspensionLogs',
            'transferBlockLogs',
            'watchlist',
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
