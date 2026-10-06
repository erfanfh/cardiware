<?php

namespace App\Application\Notification;

use App\Domain\Identity\Models\User;
use App\Domain\Notification\Models\Popup;
use Illuminate\Database\Eloquent\Collection;

class GetVisiblePopupsAction
{
    public function execute(string $targetType, ?int $targetId, ?User $user): Collection
    {
        $isVip = $user?->hasActiveVipSubscription() ?? false;
        $audiences = $user
            ? ($isVip ? ['all', 'authenticated', 'vip'] : ['all', 'authenticated'])
            : ['all', 'guest'];

        return Popup::query()
            ->with('image.primaryVariant')
            ->where('is_active', true)
            ->whereIn('audience', $audiences)
            ->where(function ($query) use ($targetType, $targetId) {
                $query->where('target_type', 'all')
                    ->orWhere(function ($query) use ($targetType, $targetId) {
                        $query->where('target_type', $targetType)
                            ->where(function ($query) use ($targetId) {
                                $query->whereNull('target_id');
                                if ($targetId !== null) {
                                    $query->orWhere('target_id', $targetId);
                                }
                            });
                    });
            })
            ->orderBy('id')
            ->get();
    }
}
