<?php

namespace App\Http\Controllers\Admin\System;

use App\Application\Notification\GetRecentPurchasesAction;
use App\Domain\Notification\Enums\CustomerSmsType;
use App\Domain\Notification\NotificationEvents;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\System\UpdateSmsNotificationSettingsRequest;
use App\Infrastructure\Services\Setting\Groups\NotificationsSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class NotificationEventSettingController extends Controller
{
    public function index(): View
    {
        Gate::authorize('setting.general');

        $settings = DB::table('notification_event_settings')->pluck('is_enabled', 'event_key');
        $notificationSettings = app(NotificationsSettings::class);

        return view('admin.system.settings.notifications', [
            'events' => NotificationEvents::LABELS,
            'settings' => $settings,
            'smsTypes' => CustomerSmsType::cases(),
            'smsSettings' => $notificationSettings,
            'recentPurchasesEnabled' => $notificationSettings->recentPurchasesEnabled(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        Gate::authorize('setting.general');
        $data = $request->validate([
            'event_key' => ['required', Rule::in(array_keys(NotificationEvents::LABELS))],
            'is_enabled' => ['required', 'boolean'],
        ]);

        DB::table('notification_event_settings')->upsert(
            [[
                'event_key' => $data['event_key'],
                'is_enabled' => $request->boolean('is_enabled'),
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['event_key'],
            ['is_enabled', 'updated_at'],
        );

        return back()->with('success', 'تنظیمات اعلان ذخیره شد.');
    }

    public function updateSms(UpdateSmsNotificationSettingsRequest $request, NotificationsSettings $settings): RedirectResponse
    {
        DB::transaction(function () use ($request, $settings): void {
            foreach (array_keys($request->validated()) as $key) {
                $settings->set($key, $request->boolean($key));
            }
        });
        $settings->forgetCache();

        return redirect()->route('admin.settings.notifications')
            ->with('success', 'تنظیمات اعلان‌های پیامکی ذخیره شد.');
    }

    public function updateRecentPurchases(Request $request, NotificationsSettings $settings): RedirectResponse
    {
        Gate::authorize('setting.general');
        $request->validate([
            'recent_purchases_enabled' => ['required', 'boolean'],
        ], [], [
            'recent_purchases_enabled' => 'نمایش خریدهای اخیر',
        ]);

        $settings->set('recent_purchases_enabled', $request->boolean('recent_purchases_enabled'));
        Cache::forget(GetRecentPurchasesAction::CACHE_KEY);

        return redirect()->route('admin.settings.notifications')
            ->with('success', 'تنظیمات نمایش خریدهای اخیر ذخیره شد.');
    }
}
