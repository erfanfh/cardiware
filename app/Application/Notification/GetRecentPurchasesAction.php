<?php

namespace App\Application\Notification;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Setting\Groups\NotificationsSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class GetRecentPurchasesAction
{
    public const CACHE_KEY = 'notifications:recent-purchases:v2';

    /** @return array<int, array{id: string, buyer: string, product: string, url: string, purchased_at: int, expires_at: int}> */
    public function execute(): array
    {
        if (! app(NotificationsSettings::class)->recentPurchasesEnabled()) {
            return [];
        }

        return Cache::remember(self::CACHE_KEY, 30, function (): array {
            $eligibleItems = fn ($query) => $query
                ->where('final_total_amount', '>', 0)
                ->whereHas('product');

            return Order::query()
                ->select(['id', 'user_id', 'paid_at'])
                ->where('status', OrderStatusEnum::PAID)
                ->where('paid_at', '>=', now()->subMinutes(30))
                ->where('paid_at', '<=', now())
                ->where('total_amount', '>', 0)
                // Only checkout sets this flag; manual transitions and free claims do not.
                ->where('metadata->purchase_notification_eligible', true)
                ->whereHas('user', fn (Builder $query) => $query->where('status', UserStatusEnum::ACTIVE))
                ->whereHas('rootItems', $eligibleItems)
                ->with([
                    'user:id,first_name,last_name',
                    'rootItems' => fn ($query) => $eligibleItems($query)
                        ->select(['id', 'order_id', 'product_id', 'parent_order_item_id'])
                        ->with('product:id,title,slug,status,published_at'),
                ])
                ->orderByDesc('paid_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->map(function (Order $order): array {
                    $product = $order->rootItems->first()->product;

                    return [
                        'id' => hash_hmac('sha256', (string) $order->id, (string) config('app.key')),
                        'buyer' => $this->publicName($order->user),
                        'product' => $product->title,
                        'url' => $product->url,
                        'purchased_at' => $order->paid_at->getTimestamp(),
                        'expires_at' => $order->paid_at->addMinutes(30)->getTimestamp(),
                    ];
                })
                ->all();
        });
    }

    private function publicName(User $user): string
    {
        $firstName = trim((string) $user->first_name);
        $lastName = trim((string) $user->last_name);
        $namePattern = '/^[\p{L}\p{M}\x{200C}\s.-]{1,255}$/u';

        if (! preg_match($namePattern, $firstName)) {
            return 'یکی از کاربران';
        }

        return preg_match($namePattern, $lastName) ? $firstName.' '.$lastName : $firstName;
    }
}
