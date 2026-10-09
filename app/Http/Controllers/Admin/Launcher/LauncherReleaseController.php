<?php

namespace App\Http\Controllers\Admin\Launcher;

use App\Domain\Launcher\Models\LauncherRelease;
use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class LauncherReleaseController extends Controller
{
    public function index(Request $request, GeneralSettings $settings): View
    {
        Gate::authorize('launcher-device.manage');

        $releases = LauncherRelease::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        $editingRelease = filled($request->query('edit'))
            ? LauncherRelease::query()->findOrFail((int)$request->query('edit'))
            : null;

        $publicDownloadUrl = trim((string)$settings->get(
            'launcher_public_download_url',
            config('launcher.public_download_url')
        ));
        $downloadMaxConnections = max(1, min(8, (int)$settings->get('launcher_download_max_connections', 8)));

        $maintenanceEnabled = (bool)$settings->get(
            'launcher_maintenance_enabled',
            config('launcher.maintenance.enabled', false)
        );
        $maintenanceMessage = (string)$settings->get(
            'launcher_maintenance_message',
            config('launcher.maintenance.message')
        );
        $maintenanceBypassMobiles = (string)$settings->get(
            'launcher_maintenance_bypass_mobiles',
            ''
        );

        $emergency = app(\App\Application\Launcher\Support\LauncherEmergencyOffline::class);
        $emergencyOfflineEnabled = $emergency->isEnabled();
        $emergencyOfflineDays = $emergency->configuredDays();

        $vpnPolicy = (string)$settings->get('launcher_vpn_policy', 'off');
        $vpnMessage = (string)$settings->get('launcher_vpn_message', '');
        $vpnExtraRanges = (string)$settings->get('launcher_vpn_extra_ranges', '');
        $ipListPath = \App\Application\Launcher\Support\IranIpRanges::path();
        $ipListUpdatedAt = is_file($ipListPath) ? \Illuminate\Support\Carbon::createFromTimestamp(filemtime($ipListPath)) : null;
        $adminIpIranian = app(\App\Application\Launcher\Support\IranIpRanges::class)->isIranian($request->ip(), $vpnExtraRanges);
        $adminIp = $request->ip();

        return view('admin.launcher.releases.index', compact(
            'vpnPolicy',
            'vpnMessage',
            'vpnExtraRanges',
            'ipListUpdatedAt',
            'adminIpIranian',
            'adminIp',
            'emergencyOfflineEnabled',
            'emergencyOfflineDays',
            'releases',
            'editingRelease',
            'publicDownloadUrl',
            'downloadMaxConnections',
            'maintenanceEnabled',
            'maintenanceMessage',
            'maintenanceBypassMobiles'
        ));
    }

    public function updateMaintenance(Request $request, GeneralSettings $settings): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'launcher_maintenance_enabled' => ['nullable', 'boolean'],
            'launcher_maintenance_message' => ['nullable', 'string', 'max:1000'],
            'launcher_maintenance_bypass_mobiles' => ['nullable', 'string', 'max:5000'],
        ], [
            'launcher_maintenance_message.max' => 'پیام حالت تعمیر نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'launcher_maintenance_bypass_mobiles.max' => 'فهرست شماره‌های استثنا خیلی بزرگ است.',
        ]);

        $enabled = $request->boolean('launcher_maintenance_enabled');
        $message = trim((string)($validated['launcher_maintenance_message'] ?? ''));
        $bypassMobiles = trim((string)($validated['launcher_maintenance_bypass_mobiles'] ?? ''));

        $settings->set('launcher_maintenance_enabled', $enabled);
        $settings->set('launcher_maintenance_message', $message !== '' ? $message : null);
        $settings->set('launcher_maintenance_bypass_mobiles', $bypassMobiles !== '' ? $bypassMobiles : null);

        return redirect()->route('admin.launcher-releases.index')
            ->with(
                'success',
                $enabled
                    ? 'حالت تعمیر لانچر فعال شد؛ کاربران تا غیرفعال‌شدن این حالت نمی‌توانند وارد لانچر شوند.'
                    : 'حالت تعمیر لانچر غیرفعال شد.'
            )
            ->with('success_title', 'حالت تعمیر لانچر');
    }

    public function updateVpnPolicy(Request $request, GeneralSettings $settings): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'launcher_vpn_policy' => ['required', Rule::in(['off', 'warn', 'block'])],
            'launcher_vpn_message' => ['nullable', 'string', 'max:500'],
            'launcher_vpn_extra_ranges' => ['nullable', 'string', 'max:20000'],
        ]);

        // Every extra range must parse, otherwise a typo silently does nothing.
        $ranges = app(\App\Application\Launcher\Support\IranIpRanges::class);
        $lines = array_values(array_filter(array_map('trim',
            preg_split('/\R/', (string)($validated['launcher_vpn_extra_ranges'] ?? '')) ?: [])));
        foreach ($lines as $line) {
            $network = explode('/', $line, 2)[0];
            if (!$ranges->cidrContains($line, $network)) {
                throw ValidationException::withMessages([
                    'launcher_vpn_extra_ranges' => "رنج «{$line}» معتبر نیست. نمونه درست: 91.98.0.0/16",
                ]);
            }
        }

        $settings->set('launcher_vpn_policy', $validated['launcher_vpn_policy']);
        $message = trim((string)($validated['launcher_vpn_message'] ?? ''));
        $settings->set('launcher_vpn_message', $message !== '' ? $message : null);
        $settings->set('launcher_vpn_extra_ranges', $lines !== [] ? implode("\n", $lines) : null);

        return redirect()->route('admin.launcher-releases.index')
            ->with('success', match ($validated['launcher_vpn_policy']) {
                'warn' => 'هشدار VPN فعال شد: کاربرانی که با IP غیر ایران وصل شوند پیام خاموش کردن VPN را می‌بینند.',
                'block' => 'مسدودسازی VPN فعال شد: کاربرانی که با IP غیر ایران وصل شوند تا خاموش کردن VPN وارد لانچر نمی‌شوند.',
                default => 'بررسی VPN خاموش شد.',
            })
            ->with('success_title', 'بررسی VPN');
    }

    public function updateEmergencyOffline(Request $request, GeneralSettings $settings): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'launcher_emergency_offline_enabled' => ['nullable', 'boolean'],
            'launcher_emergency_offline_days' => ['required', 'integer', 'min:8', 'max:30'],
        ], [
            'launcher_emergency_offline_days.min' => 'تعداد روز اضطراری باید بین ۸ تا ۳۰ باشد.',
            'launcher_emergency_offline_days.max' => 'تعداد روز اضطراری باید بین ۸ تا ۳۰ باشد.',
        ]);

        $enabled = $request->boolean('launcher_emergency_offline_enabled');
        $settings->set('launcher_emergency_offline_enabled', $enabled);
        $settings->set('launcher_emergency_offline_days', (int)$validated['launcher_emergency_offline_days']);

        return redirect()->route('admin.launcher-releases.index')
            ->with(
                'success',
                $enabled
                    ? 'حالت اضطراری فعال شد: هر کاربری که آنلاین شود مجوز آفلاین '
                        . (int)$validated['launcher_emergency_offline_days']
                        . ' روزه می‌گیرد و انتقال به دستگاه جدید تا خاموش‌شدن این حالت بسته است.'
                    : 'حالت اضطراری خاموش شد؛ مجوزهای جدید دوباره ۷ روزه هستند.'
            )
            ->with('success_title', 'حالت اضطراری قطعی اینترنت');
    }

    public function updatePublicDownload(Request $request, GeneralSettings $settings): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'launcher_public_download_url' => ['nullable', 'url:https', 'max:2048'],
            'launcher_download_max_connections' => ['nullable', 'integer', 'min:1', 'max:8'],
        ], [
            'launcher_public_download_url.url' => 'لینک دانلود عمومی لانچر باید کامل و با HTTPS باشد.',
            'launcher_download_max_connections.integer' => 'تعداد اتصال باید عدد بین ۱ تا ۸ باشد.',
        ]);

        $url = trim((string)($validated['launcher_public_download_url'] ?? ''));
        $settings->set('launcher_public_download_url', $url !== '' ? $url : null);
        $settings->set('launcher_download_max_connections',
            max(1, min(8, (int)($validated['launcher_download_max_connections'] ?? 8))));

        return redirect()->route('admin.launcher-releases.index')
            ->with('success', 'لینک دانلود عمومی لانچر با موفقیت ذخیره شد.')
            ->with('success_title', 'دانلود عمومی لانچر');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');

        // Values pasted from a PowerShell console (the signing tool's own output)
        // routinely arrive with hard line breaks at the console's wrap width, even
        // though they are meant to be a single token. Strip that whitespace before
        // validating -- previously the format regexes below rejected the whole
        // submission in that case, and because no field in the view renders
        // $errors, the form just looked like it silently did nothing.
        $strip = fn (?string $value): ?string => filled($value) ? preg_replace('/\s+/', '', $value) : $value;
        $trim = fn (?string $value): ?string => filled($value) ? trim($value) : $value;
        $request->merge([
            'version' => $trim($request->input('version')),
            'minimum_supported_version' => $trim($request->input('minimum_supported_version')),
            'download_url' => $trim($request->input('download_url')),
            'sha256' => $strip($request->input('sha256')),
            'signature' => $strip($request->input('signature')),
            'signature_key_id' => $strip($request->input('signature_key_id')),
        ]);

        $validated = $request->validate($this->rules($request), $this->messages());
        if (filled($validated['minimum_supported_version'] ?? null)
            && version_compare(
                (string)$validated['minimum_supported_version'],
                (string)$validated['version'],
                '>'
            )) {
            throw ValidationException::withMessages([
                'minimum_supported_version' =>
                    'حداقل نسخه مجاز نمی‌تواند از نسخه همین بسته بالاتر باشد.',
            ]);
        }
        $release = filled($validated['id'] ?? null)
            ? LauncherRelease::query()->findOrFail((int)$validated['id'])
            : new LauncherRelease();

        $release->fill([
            'platform' => 'windows',
            'channel' => 'stable',
            'version' => trim((string)$validated['version']),
            'minimum_supported_version' => filled($validated['minimum_supported_version'] ?? null)
                ? trim((string)$validated['minimum_supported_version'])
                : null,
            'is_mandatory' => $request->boolean('is_mandatory'),
            'is_enabled' => $request->boolean('is_enabled'),
            'download_url' => trim((string)$validated['download_url']),
            'file_name' => basename(str_replace('\\', '/', (string)$validated['file_name'])),
            'size_bytes' => (int)$validated['size_bytes'],
            'sha256' => strtolower((string)$validated['sha256']),
            'signature' => filled($validated['signature'] ?? null)
                ? preg_replace('/\s+/', '', (string)$validated['signature'])
                : null,
            'signature_key_id' => filled($validated['signature_key_id'] ?? null)
                ? strtolower(trim((string)$validated['signature_key_id']))
                : null,
            'release_notes' => filled($validated['release_notes'] ?? null)
                ? trim((string)$validated['release_notes'])
                : null,
            'published_at' => filled($validated['published_at'] ?? null)
                ? $validated['published_at']
                : ($request->boolean('is_enabled') ? now() : null),
        ])->save();

        return redirect()->route('admin.launcher-releases.index')
            ->with('success', 'نسخه لانچر با موفقیت ذخیره شد.')
            ->with('success_title', 'مدیریت آپدیت لانچر');
    }

    public function destroy(LauncherRelease $launcherRelease): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');
        $launcherRelease->delete();

        return redirect()->route('admin.launcher-releases.index')
            ->with('success', 'نسخه انتخاب‌شده حذف شد.')
            ->with('success_title', 'مدیریت آپدیت لانچر');
    }

    private function rules(Request $request): array
    {
        return [
            'id' => ['nullable', 'integer', Rule::exists('launcher_releases', 'id')],
            'version' => [
                'required',
                'string',
                'max:50',
                'regex:/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/',
                Rule::unique('launcher_releases', 'version')
                    ->where(fn($query) => $query
                        ->where('platform', 'windows')
                        ->where('channel', 'stable'))
                    ->ignore($request->integer('id')),
            ],
            'minimum_supported_version' => ['nullable', 'string', 'max:50', 'regex:/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/'],
            'download_url' => ['required', 'url:https', 'max:2048'],
            'file_name' => ['required', 'string', 'max:255', 'not_regex:/[\\\\\/:*?"<>|]/', 'regex:/\.zip$/i'],
            'size_bytes' => ['required', 'integer', 'min:1'],
            'sha256' => ['required', 'regex:/^[a-fA-F0-9]{64}$/'],
            'signature' => [
                'nullable',
                'required_with:signature_key_id',
                'string',
                'max:2048',
                'regex:/\A[A-Za-z0-9+\/]+={0,2}\z/',
            ],
            'signature_key_id' => [
                'nullable',
                'required_with:signature',
                'regex:/\A[a-fA-F0-9]{64}\z/',
            ],
            'release_notes' => ['nullable', 'string', 'max:10000'],
            'published_at' => ['nullable', 'date'],
            'is_mandatory' => ['nullable', 'boolean'],
            'is_enabled' => ['nullable', 'boolean'],
        ];
    }

    private function messages(): array
    {
        return [
            'version.regex' => 'نسخه باید مانند 1.2.0 باشد.',
            'version.unique' => 'این نسخه قبلاً برای کانال پایدار ویندوز ثبت شده است.',
            'minimum_supported_version.regex' => 'حداقل نسخه باید مانند 1.2.0 باشد.',
            'download_url.url' => 'لینک دانلود باید کامل و با HTTPS باشد.',
            'file_name.regex' => 'بسته آپدیت لانچر باید فایل ZIP باشد.',
            'sha256.regex' => 'SHA-256 باید دقیقاً ۶۴ کاراکتر هگزادسیمال باشد.',
            'signature.regex' => 'امضای بسته باید Base64 معتبر و بدون فاصله باشد.',
            'signature.required_with' => 'امضا و شناسه کلید باید با هم ثبت شوند.',
            'signature_key_id.regex' => 'شناسه کلید امضا باید دقیقاً ۶۴ کاراکتر هگزادسیمال باشد.',
            'signature_key_id.required_with' => 'امضا و شناسه کلید باید با هم ثبت شوند.',
        ];
    }
}
