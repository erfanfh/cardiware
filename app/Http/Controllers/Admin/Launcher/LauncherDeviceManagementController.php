<?php

namespace App\Http\Controllers\Admin\Launcher;

use App\Application\Launcher\Actions\Security\ResetLauncherDeviceSecurityAction;
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
        }

        return view('admin.launcher.devices.index', compact(
            'selectedUser',
            'selectedUserOption',
            'devices',
            'resetLogs',
        ));
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
