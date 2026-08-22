<?php

namespace App\Livewire\Front\Promotion;

use App\Domain\Catalog\Models\Product;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Promotion\Models\Coupon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Component;

class FloatingCouponBox extends Component
{
    public function render(): View
    {
        $coupon = Coupon::query()
            ->where('is_floating_box_enabled', true)
            ->first();

        return view('livewire.front.promotion.floating-coupon-box', [
            'promotion' => $coupon ? $this->promotionData($coupon) : null,
        ]);
    }

    private function promotionData(Coupon $coupon): array
    {
        $remaining = max(0, (int) $coupon->usage_limit - (int) $coupon->used_count);
        $state = $this->state($coupon, $remaining);
        $product = $this->firstTargetProduct($coupon);

        return [
            'id' => $coupon->getKey(),
            'title' => $coupon->title,
            'code' => $coupon->code,
            'discount_label' => $coupon->type === CouponTypeEnum::PERCENTAGE
                ? number_format((int) $coupon->value).'٪ تخفیف'
                : number_format((int) $coupon->value).' تومان تخفیف',
            'remaining' => $remaining,
            'usage_limit' => (int) $coupon->usage_limit,
            'progress' => $coupon->usage_limit
                ? min(100, max(0, ($remaining / (int) $coupon->usage_limit) * 100))
                : 0,
            'state' => $state,
            'finish_reason' => $remaining === 0 ? 'sold_out' : 'expired',
            'product_title' => $product?->title,
            'url' => $product?->url ?? front_route('products.list'),
            'starts_at' => $coupon->starts_at,
        ];
    }

    private function state(Coupon $coupon, int $remaining): string
    {
        if ($remaining === 0 || $coupon->isExpired()) {
            return 'finished';
        }

        if (! $coupon->hasStarted()) {
            return 'upcoming';
        }

        if (! $coupon->isActive()) {
            return 'inactive';
        }

        return 'active';
    }

    private function firstTargetProduct(Coupon $coupon): ?Product
    {
        $targetId = $coupon->targets()
            ->whereIn('target_type', [
                Relation::getMorphAlias(Product::class),
                Product::class,
            ])
            ->oldest('id')
            ->value('target_id');

        return $targetId ? Product::query()->find($targetId) : null;
    }
}
