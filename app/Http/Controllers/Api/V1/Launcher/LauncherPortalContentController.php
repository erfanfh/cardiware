<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Domain\Launcher\Models\LauncherGuide;
use App\Domain\Launcher\Models\LauncherNotification;
use App\Domain\Launcher\Models\LauncherNotificationRead;
use App\Http\Controllers\Controller;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class LauncherPortalContentController extends Controller
{
    public function notifications(Request $request): JsonResponse
    {
        $userId = (int)$request->user()->getKey();

        // The visible notification list is global (cached; flushed by LauncherCacheObserver,
        // short TTL for the published_at/expires_at window). Only the per-user is_read flag
        // is resolved live -- one indexed query on the reads table -- so the payload is the
        // same as the former withExists() version.
        /** @var array<int, array<string, mixed>> $visible */
        $visible = Cache::tags([CacheTag::LAUNCHER, CacheTag::LAUNCHER_PORTAL])->remember(
            CacheKey::launcherNotifications(),
            now()->addMinutes(5),
            static fn(): array => LauncherNotification::query()
                ->visible()
                ->orderByDesc('published_at')
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get()
                ->map(static fn(LauncherNotification $notification): array => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'action_text' => $notification->action_text,
                    'action_url' => $notification->action_url,
                    'published_at' => $notification->published_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
        );

        $readIds = $visible === []
            ? []
            : LauncherNotificationRead::query()
                ->where('user_id', $userId)
                ->whereIn('launcher_notification_id', array_column($visible, 'id'))
                ->pluck('launcher_notification_id')
                ->flip()
                ->all();

        $notifications = array_map(
            static fn(array $notification): array => $notification + [
                'is_read' => isset($readIds[$notification['id']]),
            ],
            $visible,
        );

        return response()->json(['data' => $notifications]);
    }

    public function markNotificationsRead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notification_ids' => ['required', 'array', 'max:200'],
            'notification_ids.*' => ['integer', 'distinct', 'exists:launcher_notifications,id'],
        ]);
        $userId = (int)$request->user()->getKey();
        $now = now();
        $rows = collect($validated['notification_ids'])->map(static fn(int $notificationId): array => [
            'launcher_notification_id' => $notificationId,
            'user_id' => $userId,
            'read_at' => $now,
        ])->all();

        DB::table((new LauncherNotificationRead())->getTable())->insertOrIgnore($rows);

        return response()->json(['data' => ['marked_read' => count($rows)]]);
    }

    public function guides(): JsonResponse
    {
        $guides = Cache::tags([CacheTag::LAUNCHER, CacheTag::LAUNCHER_PORTAL])->remember(
            CacheKey::launcherGuides(),
            now()->addMinutes(30),
            static fn(): array => LauncherGuide::query()
                ->visible()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(static fn(LauncherGuide $guide): array => [
                    'id' => $guide->id,
                    'title' => $guide->title,
                    'summary' => $guide->summary,
                    'content' => $guide->content,
                    'sort_order' => (int)$guide->sort_order,
                    'published_at' => $guide->created_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
        );

        return response()->json(['data' => $guides]);
    }
}
