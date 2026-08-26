<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Domain\Launcher\Models\LauncherGuide;
use App\Domain\Launcher\Models\LauncherNotification;
use App\Domain\Launcher\Models\LauncherNotificationRead;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class LauncherPortalContentController extends Controller
{
    public function notifications(Request $request): JsonResponse
    {
        $userId = (int)$request->user()->getKey();
        $notifications = LauncherNotification::query()
            ->visible()
            ->withExists(['reads as is_read' => fn($query) => $query->where('user_id', $userId)])
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
                'is_read' => (bool)$notification->is_read,
            ]);

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
        $guides = LauncherGuide::query()
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
            ]);

        return response()->json(['data' => $guides]);
    }
}
