<?php

namespace App\Http\Controllers\Admin\Launcher;

use App\Domain\Launcher\Models\LauncherGuide;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class LauncherGuideController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('launcher-device.manage');
        $guides = LauncherGuide::query()->orderBy('sort_order')->orderBy('id')->get();
        $editingGuide = filled($request->query('edit'))
            ? LauncherGuide::query()->findOrFail((int)$request->query('edit'))
            : null;

        return view('admin.launcher.guides.index', compact('guides', 'editingGuide'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');
        $validated = $request->validate([
            'id' => ['nullable', 'integer', Rule::exists('launcher_guides', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:50000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $guide = filled($validated['id'] ?? null)
            ? LauncherGuide::query()->findOrFail((int)$validated['id'])
            : new LauncherGuide();
        $guide->fill([
            'title' => trim((string)$validated['title']),
            'summary' => filled($validated['summary'] ?? null) ? trim((string)$validated['summary']) : null,
            'content' => trim((string)$validated['content']),
            'sort_order' => (int)($validated['sort_order'] ?? 0),
            'is_enabled' => $request->boolean('is_enabled'),
        ])->save();

        return redirect()->route('admin.launcher-guides.index')
            ->with('success', 'مطلب راهنما ذخیره شد.')
            ->with('success_title', 'راهنمای لانچر');
    }

    public function destroy(LauncherGuide $launcherGuide): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');
        $launcherGuide->delete();

        return redirect()->route('admin.launcher-guides.index')
            ->with('success', 'مطلب راهنما حذف شد.')
            ->with('success_title', 'راهنمای لانچر');
    }
}
