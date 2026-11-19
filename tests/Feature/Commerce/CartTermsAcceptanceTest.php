<?php

namespace Tests\Feature\Commerce;

use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Livewire\Front\Commerce\Cart\CartPage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class CartTermsAcceptanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group');
            $table->string('key');
            $table->json('value');
            $table->timestamps();
            $table->unique(['group', 'key']);
        });

        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('status', 30);
            $table->unsignedBigInteger('subtotal_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('taxable_amount')->default(0);
            $table->unsignedInteger('tax_rate_basis_points')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('parent_cart_item_id')->nullable();
            $table->string('purchasable_type');
            $table->unsignedBigInteger('purchasable_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('subtotal_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->json('snapshot')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('status', 30)->default('active');
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedBigInteger('withdrawable_balance')->default(0);
            $table->unsignedBigInteger('held_balance')->default(0);
            $table->unsignedBigInteger('held_withdrawable_balance')->default(0);
            $table->timestamps();
        });

        Schema::create('vip_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
        });
    }

    public function test_terms_checkbox_and_configured_link_are_shown_when_enabled(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->set('checkout_terms_enabled', true);
        $settings->set('checkout_terms_url', '/rules');

        Livewire::test(CartPage::class)
            ->assertSee('قوانین و مقررات سایت')
            ->assertSeeHtml('href="/rules"');
    }

    public function test_terms_checkbox_is_hidden_when_setting_is_disabled(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->set('checkout_terms_enabled', false);
        $settings->set('checkout_terms_url', '/rules');

        Livewire::test(CartPage::class)
            ->assertDontSee('قوانین و مقررات سایت');
    }

    public function test_checkout_is_rejected_server_side_without_required_acceptance(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->set('checkout_terms_enabled', true);
        $settings->set('checkout_terms_url', '/rules');

        $user = new User;
        $user->forceFill(['id' => 1]);
        $user->exists = true;

        Cart::query()->create([
            'user_id' => $user->id,
            'status' => CartStatusEnum::ACTIVE,
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'taxable_amount' => 0,
            'tax_rate_basis_points' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(CartPage::class)
            ->call('checkout', 'gateway', false)
            ->assertSet(
                'checkoutError',
                'برای ادامه پرداخت، پذیرش قوانین سایت الزامی است.',
            );
    }
}
