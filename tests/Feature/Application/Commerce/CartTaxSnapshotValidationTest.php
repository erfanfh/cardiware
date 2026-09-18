<?php

namespace Tests\Feature\Application\Commerce;

use App\Application\Commerce\Actions\Order\ValidateCartForOrderAction;
use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTaxSnapshotValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_tax_snapshot_is_accepted_for_order_creation(): void
    {
        $cart = $this->cartWithTax();

        app(ValidateCartForOrderAction::class)->execute($cart);

        $this->addToAssertionCount(1);
    }

    public function test_tampered_tax_amount_is_rejected(): void
    {
        $cart = $this->cartWithTax();
        $cart->forceFill(['tax_amount' => 9_001])->save();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cart total amount is invalid.');

        app(ValidateCartForOrderAction::class)->execute($cart->refresh());
    }

    private function cartWithTax(): Cart
    {
        $user = User::factory()->create();

        $reference = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'TAX-VALIDATION-' . uniqid(),
            'status' => OrderStatusEnum::PENDING_PAYMENT,
            'subtotal_amount' => 1,
            'discount_amount' => 0,
            'total_amount' => 1,
        ]);

        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'status' => CartStatusEnum::CHECKOUT_STARTED,
            'subtotal_amount' => 100_000,
            'discount_amount' => 10_000,
            'taxable_amount' => 90_000,
            'tax_rate_basis_points' => 1_000,
            'tax_amount' => 9_000,
            'total_amount' => 99_000,
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'purchasable_type' => $reference->getMorphClass(),
            'purchasable_id' => $reference->id,
            'quantity' => 1,
            'unit_price' => 90_000,
            'subtotal_amount' => 100_000,
            'discount_amount' => 10_000,
            'total_amount' => 90_000,
            'coupon_discount_amount' => 0,
            'final_total_amount' => 90_000,
        ]);

        return $cart->refresh();
    }
}
