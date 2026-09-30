<?php

namespace Tests\Feature\Commerce;

use App\Application\Commerce\Actions\Order\AddOrderDescriptionAction;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Livewire\Front\Account\MyOrdersSection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Morilog\Jalali\Jalalian;
use Tests\TestCase;

class OrderDescriptionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createTables();
    }

    public function test_legacy_description_is_preserved_and_new_entries_are_separate(): void
    {
        $user = $this->user('buyer');
        $order = $this->order($user, 'ORDER-1', 'توضیح قبلی');
        $migration = require database_path('migrations/2026_09_30_000000_create_order_descriptions_table.php');
        $migration->up();

        $action = app(AddOrderDescriptionAction::class);
        $action->execute($order, '  توضیح اول  ', $user);
        $action->execute($order, 'توضیح دوم', $user);

        $entries = $order->fresh()->descriptions;
        $this->assertCount(3, $entries);
        $this->assertSame(['توضیح دوم', 'توضیح اول', 'توضیح قبلی'], $entries->pluck('body')->all());
        $this->assertFalse($entries->first()->is_legacy);
        $this->assertTrue($entries->last()->is_legacy);
        $this->assertNotNull($entries->first()->created_at);
        $this->assertSame('توضیح قبلی', $order->fresh()->description);
    }

    public function test_customer_sees_only_their_orders_separate_dated_descriptions(): void
    {
        $user = $this->user('buyer');
        $other = $this->user('other');
        $order = $this->order($user, 'ORDER-2');
        $otherOrder = $this->order($other, 'ORDER-3');
        $migration = require database_path('migrations/2026_09_30_000000_create_order_descriptions_table.php');
        $migration->up();

        $action = app(AddOrderDescriptionAction::class);
        $action->execute($order, 'یادداشت اول');
        $action->execute($order, '<script>alert(1)</script>');
        $action->execute($otherOrder, 'توضیح سفارش دیگر');
        $date = Jalalian::fromDateTime($order->fresh()->descriptions->first()->created_at)->format('Y/m/d H:i');

        Livewire::actingAs($user)
            ->test(MyOrdersSection::class)
            ->assertSee('یادداشت اول')
            ->assertSee('<script>alert(1)</script>')
            ->assertSee($date)
            ->assertDontSee('توضیح سفارش دیگر');
    }

    public function test_admin_form_appends_notes_and_rejects_empty_text(): void
    {
        $admin = $this->user('admin');
        $order = $this->order($this->user('buyer'), 'ORDER-4');
        $migration = require database_path('migrations/2026_09_30_000000_create_order_descriptions_table.php');
        $migration->up();

        $this->actingAs($admin);
        $this->withoutMiddleware(EnsureUserIsAdmin::class);

        $this->post(route('admin.orders.descriptions.store', $order), [
            'description' => '  ',
        ])->assertSessionHasErrors('description');

        $this->post(route('admin.orders.descriptions.store', $order), [
            'description' => 'توضیح اول',
        ])->assertRedirect(route('admin.orders.edit', $order));

        $this->post(route('admin.orders.descriptions.store', $order), [
            'description' => 'توضیح دوم',
        ])->assertRedirect(route('admin.orders.edit', $order));

        $this->assertSame(['توضیح دوم', 'توضیح اول'], $order->fresh()->descriptions->pluck('body')->all());
        $this->assertDatabaseCount('order_descriptions', 2);
    }

    private function user(string $username): User
    {
        return User::query()->create([
            'username' => $username,
            'email' => $username . '@example.test',
            'password' => 'password',
            'role_id' => 1,
            'status' => UserStatusEnum::ACTIVE,
        ]);
    }

    private function order(User $user, string $number, ?string $description = null): Order
    {
        return Order::query()->create([
            'user_id' => $user->getKey(),
            'order_number' => $number,
            'status' => OrderStatusEnum::PAID,
            'subtotal_amount' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'description' => $description,
            'paid_at' => now(),
        ]);
    }

    private function createTables(): void
    {
        foreach (['order_descriptions', 'purchase_accesses', 'payments', 'order_items', 'orders', 'users', 'roles'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_super_admin')->default(false);
            $table->timestamps();
        });
        DB::table('roles')->insert([
            'id' => 1,
            'title' => 'Test administrator',
            'is_super_admin' => true,
        ]);

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username');
            $table->string('email')->nullable();
            $table->string('password');
            $table->foreignId('role_id');
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->string('order_number')->unique();
            $table->string('status');
            $table->unsignedBigInteger('subtotal_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('taxable_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedInteger('tax_rate_basis_points')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->text('description')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->unsignedBigInteger('parent_order_item_id')->nullable();
            $table->string('purchasable_type')->nullable();
            $table->unsignedBigInteger('purchasable_id')->nullable();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('purchase_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
        });
    }
}
