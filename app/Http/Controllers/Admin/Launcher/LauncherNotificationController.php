<?php

namespace App\Http\Controllers\Admin\Launcher;

use App\Domain\Launcher\Models\LauncherNotification;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class LauncherNotificationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('launcher-device.manage');
        $notifications = LauncherNotification::query()->orderByDesc('published_at')->orderBy('sort_order')->get();
        $editingNotification = filled($request->query('edit'))
            ? LauncherNotification::query()->findOrFail((int)$request->query('edit'))
            : null;

        return view('admin.launcher.notifications.index', compact('notifications', 'editingNotification'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');
        $validated = $request->validate([
            'id' => ['nullable', 'integer', Rule::exists('launcher_notifications', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['required', Rule::in(['info', 'success', 'warning', 'important'])],
            'action_text' => ['nullable', 'string', 'max:80'],
            'action_url' => ['nullable', 'url:https', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => [
                'nullable',
                'date',
                $request->filled('published_at') ? 'after:published_at' : 'after:now',
            ],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $notification = filled($validated['id'] ?? null)
            ? LauncherNotification::query()->findOrFail((int)$validated['id'])
            : new LauncherNotification();
        $notification->fill([
            'title' => trim((string)$validated['title']),
            'message' => trim((string)$validated['message']),
            'type' => $validated['type'],
            'action_text' => filled($validated['action_text'] ?? null) ? trim((string)$validated['action_text']) : null,
            'action_url' => filled($validated['action_url'] ?? null) ? trim((string)$validated['action_url']) : null,
            'sort_order' => (int)($validated['sort_order'] ?? 0),
            'published_at' => $validated['published_at'] ?? ($request->boolean('is_enabled') ? now() : null),
            'expires_at' => $validated['expires_at'] ?? null,
            'is_enabled' => $request->boolean('is_enabled'),
        ])->save();

        return redirect()->route('admin.launcher-notifications.index')
            ->with('success', 'اعلان لانچر ذخیره شد.')
            ->with('success_title', 'اعلانات لانچر');
    }

    public function destroy(LauncherNotification $launcherNotification): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');
        $launcherNotification->delete();

        return redirect()->route('admin.launcher-notifications.index')
            ->with('success', 'اعلان حذف شد.')
            ->with('success_title', 'اعلانات لانچر');
    }
}
