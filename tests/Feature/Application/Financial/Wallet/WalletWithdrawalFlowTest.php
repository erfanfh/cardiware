<?php

namespace Tests\Feature\Application\Financial\Wallet;

use App\Application\Financial\Actions\Wallet\CreateWalletWithdrawalAction;
use App\Domain\Financial\Enums\WalletHoldStatusEnum;
use App\Domain\Financial\Enums\WalletStatusEnum;
use App\Domain\Financial\Enums\WalletWithdrawalStatusEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletHold;
use App\Domain\Financial\Models\WalletTransaction;
use App\Domain\Financial\Models\WalletWithdrawal;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\HandleSeoRedirectsMiddleware;
use App\Infrastructure\Services\Setting\Groups\FinancialSettings;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class WalletWithdrawalFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['user_notifications', 'notification_event_settings', 'wallet_withdrawals', 'wallet_holds', 'wallet_transactions', 'wallets', 'settings', 'users', 'roles'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::create('roles', function (Blueprint $table): void {
            $table->id(); $table->string('title'); $table->boolean('is_default')->default(false);
            $table->boolean('is_super_admin')->default(false); $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id(); $table->string('username'); $table->string('first_name');
            $table->string('last_name'); $table->string('password');
            $table->string('status'); $table->foreignId('role_id');
            $table->softDeletes(); $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table): void {
            $table->id(); $table->string('group'); $table->string('key');
            $table->text('value')->nullable(); $table->timestamps();
            $table->unique(['group', 'key']);
        });
        Schema::create('wallets', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->unique(); $table->string('status');
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedBigInteger('withdrawable_balance')->default(0);
            $table->unsignedBigInteger('held_balance')->default(0);
            $table->unsignedBigInteger('held_withdrawable_balance')->default(0);
            $table->timestamps();
        });
        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id(); $table->string('public_token')->unique();
            $table->foreignId('wallet_id'); $table->foreignId('actor_user_id')->nullable();
            $table->string('type'); $table->string('direction');
            $table->unsignedBigInteger('amount'); $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('withdrawable_amount')->default(0);
            $table->unsignedBigInteger('balance_before'); $table->unsignedBigInteger('balance_after');
            $table->unsignedBigInteger('withdrawable_balance_before');
            $table->unsignedBigInteger('withdrawable_balance_after');
            $table->nullableMorphs('reference'); $table->string('idempotency_key')->unique();
            $table->char('idempotency_fingerprint', 64); $table->text('description')->nullable();
            $table->json('metadata')->nullable(); $table->timestamp('created_at')->nullable();
        });
        Schema::create('wallet_holds', function (Blueprint $table): void {
            $table->id(); $table->string('public_token')->unique(); $table->foreignId('wallet_id');
            $table->foreignId('actor_user_id')->nullable(); $table->string('type');
            $table->string('status'); $table->string('debit_policy');
            $table->unsignedBigInteger('amount'); $table->unsignedBigInteger('withdrawable_amount');
            $table->nullableMorphs('reference'); $table->foreignId('captured_transaction_id')->nullable();
            $table->foreignId('captured_by_user_id')->nullable(); $table->foreignId('released_by_user_id')->nullable();
            $table->string('idempotency_key')->unique(); $table->char('idempotency_fingerprint', 64);
            $table->text('description')->nullable(); $table->text('release_reason')->nullable();
            $table->json('metadata')->nullable(); $table->timestamp('expires_at')->nullable();
            $table->timestamp('captured_at')->nullable(); $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_30_120000_create_wallet_withdrawals_table.php'))->up();
        (require database_path('migrations/2026_10_02_120000_add_card_number_to_wallet_withdrawals_table.php'))->up();
        (require database_path('migrations/2026_10_02_130000_create_in_app_notifications_tables.php'))->up();
        app(FinancialSettings::class)->forgetCache();
        $this->withoutMiddleware(EnsureUserIsAdmin::class);
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_request_reserves_funds_then_approval_and_payment_capture_once(): void
    {
        [$user, $wallet] = $this->fundedUser();
        $this->enableWithdrawals();
        $key = (string) Str::uuid();
        $payload = $this->payload($key);
        $persianIbanDigits = strtr(substr($payload['iban'], 2), [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $persianCardNumber = strtr(implode(' ', str_split($payload['card_number'], 4)), [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);

        $this->actingAs($user)->post(route('account.wallet.withdrawals.store'), [
            ...$payload, 'amount' => '۱۰۰٬۰۰۰', 'iban' => $persianIbanDigits,
            'card_number' => $persianCardNumber,
        ])
            ->assertRedirect(route('account.wallet') . '#wallet-withdrawal')
            ->assertSessionHas('wallet_withdrawal_success');
        $this->post(route('account.wallet.withdrawals.store'), $payload)->assertRedirect();

        $withdrawal = WalletWithdrawal::query()->firstOrFail();
        $this->assertSame($payload['iban'], $withdrawal->iban);
        $this->assertSame($payload['card_number'], $withdrawal->card_number);
        $this->assertSame('**** **** **** 7890', $withdrawal->maskedCardNumber());
        $this->assertSame(WalletWithdrawalStatusEnum::PENDING, $withdrawal->status);
        $this->assertSame(1, WalletWithdrawal::query()->unresolved()->count());
        $this->assertSame(200000, $wallet->fresh()->balance);
        $this->assertSame(100000, $wallet->fresh()->held_balance);
        $this->assertSame(100000, $wallet->fresh()->available_withdrawable_balance);
        $this->assertSame(WalletHoldStatusEnum::ACTIVE, $withdrawal->hold->status);
        $this->assertDatabaseCount('wallet_withdrawals', 1);
        $this->assertDatabaseCount('wallet_holds', 1);
        $this->assertDatabaseCount('wallet_transactions', 0);
        $this->assertNotSame($withdrawal->iban, DB::table('wallet_withdrawals')->value('iban'));
        $this->assertNotSame($withdrawal->card_number, DB::table('wallet_withdrawals')->value('card_number'));

        $admin = $this->makeUser(true);
        $this->actingAs($admin)->post(route('admin.wallet-withdrawals.approve', $withdrawal))->assertRedirect();
        $this->assertSame(WalletWithdrawalStatusEnum::APPROVED, $withdrawal->fresh()->status);
        $this->assertSame(1, WalletWithdrawal::query()->unresolved()->count());
        $this->assertSame(100000, $wallet->fresh()->held_balance);

        $this->post(route('admin.wallet-withdrawals.pay', $withdrawal), [
            'payment_reference' => 'BANK-123', 'admin_note' => 'مبلغ به شبای ثبت‌شده واریز شد.',
        ])->assertRedirect();
        $this->post(route('admin.wallet-withdrawals.pay', $withdrawal), [
            'payment_reference' => 'BANK-123', 'admin_note' => 'مبلغ به شبای ثبت‌شده واریز شد.',
        ])->assertRedirect();

        $this->assertSame(WalletWithdrawalStatusEnum::PAID, $withdrawal->fresh()->status);
        $this->assertSame(0, WalletWithdrawal::query()->unresolved()->count());
        $this->assertSame('BANK-123', $withdrawal->fresh()->payment_reference);
        $this->assertSame(100000, $wallet->fresh()->balance);
        $this->assertSame(0, $wallet->fresh()->held_balance);
        $this->assertSame(WalletHoldStatusEnum::CAPTURED, $withdrawal->hold->fresh()->status);
        $this->assertDatabaseCount('wallet_transactions', 1);
        $this->assertSame($admin->id, $withdrawal->fresh()->paid_by_user_id);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $user->id, 'event_key' => 'withdrawal.resolved', 'subject_id' => $withdrawal->id]);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->withoutMiddleware(HandleSeoRedirectsMiddleware::class);
        $this->actingAs($user);
        $notificationId = (int) DB::table('user_notifications')->value('id');
        $this->post(route('account.notifications.read', $notificationId), [
            'return_to' => 'wallet', 'withdrawals_page' => 1,
        ])->assertRedirect(route('account.wallet').'#wallet-withdrawal-history');
        $this->assertNotNull(DB::table('user_notifications')->value('read_at'));
    }

    public function test_rejection_releases_hold_and_disabling_only_blocks_new_requests(): void
    {
        [$user, $wallet] = $this->fundedUser();
        $this->enableWithdrawals();
        $this->actingAs($user)->post(route('account.wallet.withdrawals.store'), $this->payload((string) Str::uuid()))->assertRedirect();
        $withdrawal = WalletWithdrawal::query()->firstOrFail();
        app(FinancialSettings::class)->set('withdrawal_enabled', false);

        $this->post(route('account.wallet.withdrawals.store'), $this->payload((string) Str::uuid()))
            ->assertRedirect(route('account.wallet') . '#wallet-withdrawal')
            ->assertSessionHasErrorsIn('walletWithdrawal', 'withdrawal')
            ->assertSessionDoesntHaveErrors('withdrawal');
        $this->assertDatabaseCount('wallet_withdrawals', 1);

        $admin = $this->makeUser(true);
        $this->actingAs($admin)->post(route('admin.wallet-withdrawals.reject', $withdrawal), [
            'reason' => 'اطلاعات حساب نیاز به اصلاح دارد.',
        ])->assertRedirect();
        $this->assertSame(WalletWithdrawalStatusEnum::REJECTED, $withdrawal->fresh()->status);
        $this->assertSame(0, WalletWithdrawal::query()->unresolved()->count());
        $this->assertSame(0, $wallet->fresh()->held_balance);
        $this->assertSame(200000, $wallet->fresh()->balance);
        $this->assertSame(WalletHoldStatusEnum::RELEASED, $withdrawal->hold->fresh()->status);
        $this->assertDatabaseCount('wallet_transactions', 0);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $user->id, 'event_key' => 'withdrawal.resolved', 'subject_id' => $withdrawal->id]);
    }

    public function test_invalid_iban_and_insufficient_balance_do_not_create_a_hold(): void
    {
        [$user, $wallet] = $this->fundedUser();
        $this->enableWithdrawals();
        $this->actingAs($user)->post(route('account.wallet.withdrawals.store'), [
            ...$this->payload((string) Str::uuid()), 'iban' => 'IR' . str_repeat('0', 25),
        ])->assertSessionHasErrorsIn('walletWithdrawal', 'iban');
        $this->post(route('account.wallet.withdrawals.store'), [
            ...$this->payload((string) Str::uuid()), 'iban' => '12345678901234567890123',
        ])->assertSessionHasErrorsIn('walletWithdrawal', 'iban');
        $this->post(route('account.wallet.withdrawals.store'), [
            ...$this->payload((string) Str::uuid()), 'card_number' => '123456789012345',
        ])->assertSessionHasErrorsIn('walletWithdrawal', 'card_number');
        $payloadWithoutCard = $this->payload((string) Str::uuid());
        unset($payloadWithoutCard['card_number']);
        $this->post(route('account.wallet.withdrawals.store'), $payloadWithoutCard)
            ->assertSessionHasErrorsIn('walletWithdrawal', 'card_number');
        $this->post(route('account.wallet.withdrawals.store'), [
            ...$this->payload((string) Str::uuid()), 'amount' => 10000,
        ])->assertRedirect(route('account.wallet') . '#wallet-withdrawal')
            ->assertSessionHasErrorsIn('walletWithdrawal', 'amount')
            ->assertSessionDoesntHaveErrors('amount');
        $this->post(route('account.wallet.withdrawals.store'), [
            ...$this->payload((string) Str::uuid()), 'amount' => 21000000,
        ])->assertSessionHasErrorsIn('walletWithdrawal', 'amount');
        $this->post(route('account.wallet.withdrawals.store'), [
            ...$this->payload((string) Str::uuid()), 'amount' => 250000,
        ])->assertSessionHasErrorsIn('walletWithdrawal', 'amount');
        $this->assertDatabaseCount('wallet_withdrawals', 0);
        $this->assertDatabaseCount('wallet_holds', 0);
        $this->assertSame(0, $wallet->fresh()->held_balance);
    }

    public function test_conflicting_request_key_is_rejected(): void
    {
        [$user] = $this->fundedUser();
        $this->enableWithdrawals();
        $key = (string) Str::uuid();
        $action = app(CreateWalletWithdrawalAction::class);
        $action->execute($user, 100000, $this->iban(), $this->cardNumber(), 'کاربر آزمایشی', null, $key);

        $this->expectException(DomainException::class);
        $action->execute($user, 110000, $this->iban(), $this->cardNumber(), 'کاربر آزمایشی', null, $key);
    }

    public function test_same_request_key_cannot_be_reused_with_another_card(): void
    {
        [$user] = $this->fundedUser();
        $this->enableWithdrawals();
        $key = (string) Str::uuid();
        $action = app(CreateWalletWithdrawalAction::class);
        $action->execute($user, 100000, $this->iban(), $this->cardNumber(), 'کاربر آزمایشی', null, $key);

        try {
            $action->execute($user, 100000, $this->iban(), '6037991234567891', 'کاربر آزمایشی', null, $key);
            $this->fail('A different card number was accepted for the same request key.');
        } catch (DomainException) {
            $this->assertDatabaseCount('wallet_withdrawals', 1);
            $this->assertDatabaseCount('wallet_holds', 1);
        }
    }

    public function test_existing_withdrawal_without_card_remains_readable(): void
    {
        [$user, $wallet] = $this->fundedUser();
        $withdrawal = WalletWithdrawal::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'idempotency_key' => 'wallet-withdrawal:' . Str::uuid(),
            'status' => WalletWithdrawalStatusEnum::PENDING,
            'amount' => 100000,
            'iban' => $this->iban(),
            'account_holder' => 'کاربر آزمایشی',
        ]);

        $this->assertNull($withdrawal->fresh()->card_number);
        $this->assertNull($withdrawal->maskedCardNumber());
    }

    public function test_admin_can_toggle_requests_and_rejected_request_cannot_be_paid(): void
    {
        [$user, $wallet] = $this->fundedUser();
        $admin = $this->makeUser(true);
        $this->actingAs($admin)->put(route('admin.wallet-withdrawals.settings'), [
            'withdrawal_enabled' => '1',
        ])->assertRedirect();
        $this->assertTrue(app(FinancialSettings::class)->get('withdrawal_enabled'));

        $this->actingAs($user)->post(route('account.wallet.withdrawals.store'), $this->payload((string) Str::uuid()))->assertRedirect();
        $withdrawal = WalletWithdrawal::query()->firstOrFail();
        $this->actingAs($admin)->post(route('admin.wallet-withdrawals.reject', $withdrawal), [
            'reason' => 'رد درخواست',
        ])->assertRedirect();
        $this->post(route('admin.wallet-withdrawals.pay', $withdrawal), [
            'payment_reference' => 'BANK-999', 'admin_note' => 'تلاش نامعتبر',
        ])->assertSessionHas('error');
        $this->assertSame(WalletWithdrawalStatusEnum::REJECTED, $withdrawal->fresh()->status);
        $this->assertSame(200000, $wallet->fresh()->balance);
        $this->assertDatabaseCount('wallet_transactions', 0);

        $this->put(route('admin.wallet-withdrawals.settings'), ['withdrawal_enabled' => '0'])->assertRedirect();
        $this->assertFalse(app(FinancialSettings::class)->get('withdrawal_enabled'));
    }

    public function test_withdrawal_uses_only_withdrawable_balance(): void
    {
        [$user, $wallet] = $this->fundedUser();
        $wallet->forceFill(['withdrawable_balance' => 50000])->save();
        $this->enableWithdrawals();

        $this->actingAs($user)->post(route('account.wallet.withdrawals.store'), $this->payload((string) Str::uuid()))
            ->assertRedirect(route('account.wallet') . '#wallet-withdrawal')
            ->assertSessionHasErrorsIn('walletWithdrawal', 'amount')
            ->assertSessionDoesntHaveErrors('amount');
        $this->assertDatabaseCount('wallet_withdrawals', 0);
        $this->assertSame(0, $wallet->fresh()->held_balance);
    }

    public function test_regular_user_cannot_resolve_withdrawal_or_change_settings(): void
    {
        [$user] = $this->fundedUser();
        $this->enableWithdrawals();
        $this->actingAs($user)->post(route('account.wallet.withdrawals.store'), $this->payload((string) Str::uuid()))->assertRedirect();
        $withdrawal = WalletWithdrawal::query()->firstOrFail();

        $this->post(route('admin.wallet-withdrawals.approve', $withdrawal))->assertForbidden();
        $this->put(route('admin.wallet-withdrawals.settings'), ['withdrawal_enabled' => '0'])->assertForbidden();
        $this->assertSame(WalletWithdrawalStatusEnum::PENDING, $withdrawal->fresh()->status);
        $this->assertTrue(app(FinancialSettings::class)->get('withdrawal_enabled'));
    }

    public function test_legacy_withdrawal_url_opens_the_wallet_section(): void
    {
        $user = $this->makeUser();
        $this->withoutMiddleware(HandleSeoRedirectsMiddleware::class);

        $this->actingAs($user)->get(route('account.wallet.withdrawals.index'))
            ->assertRedirect(route('account.wallet') . '#wallet-withdrawal');
    }

    public function test_embedded_withdrawal_cards_render_with_request_history(): void
    {
        [$user, $wallet] = $this->fundedUser();
        $this->enableWithdrawals();
        $withdrawal = app(CreateWalletWithdrawalAction::class)->execute(
            $user, 100000, $this->iban(), $this->cardNumber(), 'کاربر آزمایشی', null, (string) Str::uuid(),
        );
        $withdrawals = WalletWithdrawal::query()->where('user_id', $user->id)
            ->paginate(5, ['*'], 'withdrawals_page');

        $errors = new \Illuminate\Support\ViewErrorBag();
        $errors->put('default', new \Illuminate\Support\MessageBag(['amount' => 'خطای فرم شارژ']));
        $errors->put('walletWithdrawal', new \Illuminate\Support\MessageBag(['iban' => 'خطای فرم برداشت']));
        $withdrawalNotificationIds = collect([$withdrawal->id => 123]);
        $html = view('front.account.partials.wallet-withdrawal', compact('user', 'wallet', 'withdrawals', 'errors', 'withdrawalNotificationIds'))->render();

        $this->assertStringContainsString('id="wallet-withdrawal"', $html);
        $this->assertStringContainsString('id="wallet-withdrawal-history"', $html);
        $this->assertStringContainsString('نام صاحب حساب', $html);
        $this->assertStringContainsString('name="card_number"', $html);
        $this->assertStringContainsString('name="iban" type="text" inputmode="numeric"', $html);
        $this->assertStringContainsString('aria-hidden="true">IR</span>', $html);
        $this->assertStringContainsString('novalidate', $html);
        $this->assertStringNotContainsString('name="amount" type="number"', $html);
        $this->assertStringNotContainsString(' required', $html);
        $this->assertStringNotContainsString(' maxlength=', $html);
        $this->assertStringContainsString($withdrawal->maskedIban(), $html);
        $this->assertStringContainsString('نتیجه جدید', $html);
        $this->assertStringContainsString($withdrawal->maskedCardNumber(), $html);
        $this->assertStringContainsString('در انتظار بررسی', $html);
        $this->assertStringContainsString('خطای فرم برداشت', $html);
        $this->assertStringNotContainsString('خطای فرم شارژ', $html);
    }

    private function fundedUser(): array
    {
        $user = $this->makeUser();
        $wallet = Wallet::query()->create(['user_id' => $user->id, 'status' => WalletStatusEnum::ACTIVE]);
        $wallet->forceFill(['balance' => 200000, 'withdrawable_balance' => 200000])->save();
        return [$user, $wallet];
    }

    private function makeUser(bool $admin = false): User
    {
        $roleId = DB::table('roles')->insertGetId([
            'title' => $admin ? 'مدیر' : 'کاربر', 'is_default' => !$admin,
            'is_super_admin' => $admin, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return User::query()->create([
            'username' => (string) Str::ulid(), 'first_name' => 'کاربر', 'last_name' => 'آزمایشی',
            'password' => 'password', 'status' => UserStatusEnum::ACTIVE, 'role_id' => $roleId,
        ]);
    }

    private function enableWithdrawals(): void
    {
        app(FinancialSettings::class)->set('withdrawal_enabled', true);
    }

    private function payload(string $key): array
    {
        return [
            'amount' => 100000, 'iban' => $this->iban(),
            'card_number' => $this->cardNumber(),
            'account_holder' => 'کاربر آزمایشی', 'user_note' => 'برداشت موجودی',
            'request_key' => $key,
        ];
    }

    private function iban(): string
    {
        $bban = '0123456789012345678901';
        for ($check = 0; $check < 100; $check++) {
            $candidate = str_pad((string) $check, 2, '0', STR_PAD_LEFT) . $bban;
            $remainder = 0;
            foreach (str_split($candidate . '1827') as $digit) {
                $remainder = ($remainder * 10 + (int) $digit) % 97;
            }
            if ($remainder === 1) {
                return 'IR' . $candidate;
            }
        }
        throw new \LogicException('Unable to build a test IBAN.');
    }

    private function cardNumber(): string
    {
        return '6037991234567890';
    }
}
