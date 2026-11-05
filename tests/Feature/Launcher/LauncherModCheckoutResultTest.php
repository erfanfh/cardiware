<?php

namespace Tests\Feature\Launcher;

use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Launcher\Models\LauncherCheckoutSession;
use App\Domain\Launcher\Models\LauncherMod;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * خرید تکی ماد از لانچر: پرداخت موفق باید به لانچر برگردد، حتی اگر ورود و درگاه
 * بیشتر از مهلت شروع خرید طول بکشد.
 */
class LauncherModCheckoutResultTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mod_checkout_test', 'database.connections.mod_checkout_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);

        foreach ([new Order, new OrderItem] as $model) {
            Schema::create($model->getTable(), function (Blueprint $table) use ($model): void {
                $table->id();
                foreach ($model->getFillable() as $column) {
                    $table->text($column)->nullable();
                }
                $table->timestamps();
            });
        }

        DB::table('orders')->insert(['id' => 1, 'user_id' => 1]);
    }

    protected function tearDown(): void
    {
        DB::purge('mod_checkout_test');
        parent::tearDown();
    }

    private function checkout(array $attributes): LauncherCheckoutSession
    {
        return (new LauncherCheckoutSession)->forceFill(array_merge([
            'user_id' => 1,
            'product_sale_item_id' => 5,
            'launcher_mod_id' => null,
            'expires_at' => now()->addMinutes(30),
            'consumed_at' => null,
        ], $attributes));
    }

    private function orderItem(string $type, int $id): void
    {
        DB::table('order_items')->insert(['order_id' => 1, 'purchasable_type' => $type, 'purchasable_id' => $id]);
    }

    public function test_a_mod_only_order_matches_the_mod_checkout(): void
    {
        $this->orderItem(LauncherMod::MORPH_ALIAS, 42);
        $order = Order::query()->findOrFail(1);

        $this->assertTrue($this->checkout(['launcher_mod_id' => 42])->isPaidBy($order));
        // همان سفارش برای خرید نسخه‌ی مادر (یا ماد دیگری) تطبیق نمی‌خورد.
        $this->assertFalse($this->checkout([])->isPaidBy($order));
        $this->assertFalse($this->checkout(['launcher_mod_id' => 43])->isPaidBy($order));
    }

    public function test_a_sale_item_order_still_matches_the_product_checkout(): void
    {
        $this->orderItem('product_sale_item', 5);
        $order = Order::query()->findOrFail(1);

        $this->assertTrue($this->checkout([])->isPaidBy($order));
        $this->assertFalse($this->checkout(['launcher_mod_id' => 42])->isPaidBy($order));
    }

    public function test_result_is_delivered_for_a_day_after_the_start_window_but_only_once(): void
    {
        $this->assertTrue($this->checkout(['expires_at' => now()->subHours(3)])->canDeliverResult());
        $this->assertFalse($this->checkout(['expires_at' => now()->subHours(25)])->canDeliverResult());
        $this->assertFalse($this->checkout(['consumed_at' => now()])->canDeliverResult());
    }
}
