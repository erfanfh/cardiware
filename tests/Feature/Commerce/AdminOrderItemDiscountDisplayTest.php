<?php

namespace Tests\Feature\Commerce;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminOrderItemDiscountDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create(['is_super_admin' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
    }

    public static function itemAmounts(): array
    {
        return [
            'stacked coupons and tax' => [1, 200000, 0, 110000, 9000],
            'product and coupon discounts' => [1, 200000, 20000, 18000, 0],
            'fully discounted multiple units' => [2, 100000, 0, 200000, 0],
            'no discount' => [2, 100000, 0, 0, 0],
        ];
    }

    #[DataProvider('itemAmounts')]
    public function test_item_row_shows_all_discounts_and_net_total_without_changing_order_data(
        int $quantity, int $unitPrice, int $productDiscount, int $couponDiscount, int $tax,
    ): void {
        $subtotal = $quantity * $unitPrice;
        $discount = $productDiscount + $couponDiscount;
        $net = $subtotal - $discount;
        $order = $this->order($subtotal, $discount, $net, $tax);
        $item = $this->item($order, [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal_amount' => $subtotal,
            'discount_amount' => $productDiscount,
            'coupon_discount_amount' => $couponDiscount,
            'total_amount' => $subtotal - $productDiscount,
            'final_total_amount' => $net,
        ]);
        $orderBefore = $order->fresh()->getAttributes();
        $itemBefore = $item->fresh()->getAttributes();

        $response = $this->get(route('admin.orders.edit', $order))->assertOk();
        $xpath = $this->xpath($response->getContent());
        $table = '//table[.//th[contains(., "قیمت واحد")]]';
        foreach ([2 => (string) $quantity, 3 => $this->money($unitPrice), 4 => $this->money($discount), 5 => $this->money($net)] as $cell => $expected) {
            $this->assertSame($expected, trim($xpath->evaluate("string($table/tbody/tr[1]/td[$cell])")));
        }
        $this->assertSame($this->money($net + $tax), trim($xpath->evaluate("string($table/tfoot/tr[last()]/td[2])")));
        $this->assertSame($tax > 0 ? 2 : 1, $xpath->query("$table/tfoot/tr")->length);
        if ($tax > 0) {
            $this->assertStringContainsString('مالیات (10٪)', $xpath->evaluate("string($table/tfoot/tr[1]/td[1])"));
            $this->assertSame($this->money($tax), trim($xpath->evaluate("string($table/tfoot/tr[1]/td[2])")));
        }
        $this->assertSame($orderBefore, $order->fresh()->getAttributes());
        $this->assertSame($itemBefore, $item->fresh()->getAttributes());
    }

    public function test_addon_displays_its_own_combined_discount_and_net_amount(): void
    {
        $order = $this->order(150000, 40000, 110000);
        $parent = $this->item($order, [
            'unit_price' => 100000, 'subtotal_amount' => 100000,
            'coupon_discount_amount' => 10000, 'total_amount' => 100000, 'final_total_amount' => 90000,
        ]);
        $this->item($order, [
            'parent_order_item_id' => $parent->id,
            'purchasable_type' => 'product_addon',
            'snapshot' => ['title' => 'Discounted installation'],
            'unit_price' => 50000, 'subtotal_amount' => 50000,
            'discount_amount' => 10000, 'coupon_discount_amount' => 20000,
            'total_amount' => 40000, 'final_total_amount' => 20000,
        ]);

        $response = $this->get(route('admin.orders.edit', $order))->assertOk();
        $xpath = $this->xpath($response->getContent());
        $table = '//table[.//th[contains(., "قیمت واحد")]]';
        $this->assertSame('10,000 تومان', trim($xpath->evaluate("string($table/tbody/tr/td[4])")));
        $this->assertSame('90,000 تومان', trim($xpath->evaluate("string($table/tbody/tr/td[5])")));
        $addon = $xpath->evaluate("string($table/tbody/tr/td[1])");
        $this->assertStringContainsString('Discounted installation', $addon);
        $this->assertStringContainsString('تخفیف: 30,000 تومان', $addon);
        $this->assertStringContainsString('جمع: 20,000 تومان', $addon);
        $this->assertSame('110,000 تومان', trim($xpath->evaluate("string($table/tfoot/tr/td[2])")));
    }

    private function order(int $subtotal, int $discount, int $net, int $tax = 0): Order
    {
        return Order::query()->create([
            'user_id' => auth()->id(), 'order_number' => 'DISPLAY-1069',
            'status' => OrderStatusEnum::PENDING_PAYMENT,
            'subtotal_amount' => $subtotal, 'discount_amount' => $discount,
            'taxable_amount' => $net, 'tax_rate_basis_points' => $tax > 0 ? 1000 : 0,
            'tax_amount' => $tax, 'total_amount' => $net + $tax,
        ]);
    }

    private function item(Order $order, array $attributes): OrderItem
    {
        return $order->items()->create([
            'purchasable_type' => 'vip_plan', 'purchasable_id' => 1, 'quantity' => 1,
            'discount_amount' => 0, 'snapshot' => ['title' => 'Discounted item'],
            ...$attributes,
        ]);
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    private function money(int $amount): string
    {
        return number_format($amount).' تومان';
    }
}
