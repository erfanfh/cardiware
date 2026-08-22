<?php

namespace App\Http\Controllers\Admin\Launcher;

use App\Domain\Launcher\Models\LauncherRelease;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class LauncherReleaseController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('launcher-device.manage');

        $releases = LauncherRelease::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        $editingRelease = filled($request->query('edit'))
            ? LauncherRelease::query()->findOrFail((int)$request->query('edit'))
            : null;

        return view('admin.launcher.releases.index', compact('releases', 'editingRelease'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');

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
