<?php

namespace Tests\Feature\Application\Financial\Wallet;

use App\Application\Financial\Actions\Wallet\DebitWalletAction;
use App\Application\Financial\DTOs\DebitWalletDTO;
use App\Application\Financial\Exceptions\WalletUnavailableException;
use App\Domain\Financial\Enums\WalletStatusEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletTransaction;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminWalletAdjustmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['wallet_transactions', 'wallets', 'permission_role', 'permissions', 'users', 'roles'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_super_admin')->default(false);
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('password');
            $table->string('status');
            $table->foreignId('role_id');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('group');
            $table->string('ability');
            $table->string('label');
            $table->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $table): void {
            $table->foreignId('permission_id');
            $table->foreignId('role_id');
        });
        Schema::create('wallets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique();
            $table->string('status');
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedBigInteger('withdrawable_balance')->default(0);
            $table->unsignedBigInteger('held_balance')->default(0);
            $table->unsignedBigInteger('held_withdrawable_balance')->default(0);
            $table->timestamps();
        });
        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('public_token')->unique();
            $table->foreignId('wallet_id');
            $table->foreignId('actor_user_id')->nullable();
            $table->string('type');
            $table->string('direction');
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('withdrawable_amount')->default(0);
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');
            $table->unsignedBigInteger('withdrawable_balance_before');
            $table->unsignedBigInteger('withdrawable_balance_after');
            $table->nullableMorphs('reference');
            $table->string('idempotency_key')->unique();
            $table->char('idempotency_fingerprint', 64);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        $this->withoutMiddleware(EnsureUserIsAdmin::class);
    }

    public function test_credit_and_debit_record_actor_reason_and_preserve_held_balance(): void
    {
        $admin = $this->makeUser(true);
        $user = $this->makeUser();
        $this->actingAs($admin);

        $this->post(route('admin.users.wallet.adjust', $user), $this->payload('credit', 1000, 'اعتبار اولیه', '1'))
            ->assertRedirect(route('admin.users.wallet.show', $user));

        $wallet = Wallet::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(1000, $wallet->balance);
        $this->assertSame(1000, $wallet->withdrawable_balance);

        $wallet->forceFill(['held_balance' => 300, 'held_withdrawable_balance' => 300])->save();
        $this->post(route('admin.users.wallet.adjust', $user), $this->payload('debit', 400, 'اصلاح حساب'))
            ->assertRedirect(route('admin.users.wallet.show', $user));

        $wallet->refresh();
        $this->assertSame(600, $wallet->balance);
        $this->assertSame(600, $wallet->withdrawable_balance);
        $this->assertSame(300, $wallet->held_balance);
        $this->assertSame(2, WalletTransaction::query()->count());
        $this->post(route('admin.users.wallet.adjust', $user), $this->payload('debit', 301, 'بیش از موجودی آزاد'))
            ->assertSessionHasErrors('amount');
        $this->assertSame(600, $wallet->fresh()->balance);
        $debit = WalletTransaction::query()->latest('id')->firstOrFail();
        $this->assertSame(WalletTransactionTypeEnum::ADMIN_DEBIT, $debit->type);
        $this->assertSame($admin->id, $debit->actor_user_id);
        $this->assertSame('اصلاح حساب', $debit->description);
        $this->assertSame(1000, $debit->balance_before);
        $this->assertSame(600, $debit->balance_after);
    }

    public function test_non_withdrawable_credit_is_consumed_first_and_duplicate_request_is_safe(): void
    {
        $admin = $this->makeUser(true);
        $user = $this->makeUser();
        $this->actingAs($admin);
        $credit = $this->payload('credit', 500, 'اعتبار هدیه', '0');

        $this->post(route('admin.users.wallet.adjust', $user), $credit)->assertRedirect();
        $this->post(route('admin.users.wallet.adjust', $user), $credit)->assertRedirect();
        $this->assertDatabaseCount('wallet_transactions', 1);

        $this->post(route('admin.users.wallet.adjust', $user), $this->payload('debit', 200, 'اصلاح اعتبار'))
            ->assertRedirect();
        $wallet = Wallet::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(300, $wallet->balance);
        $this->assertSame(0, $wallet->withdrawable_balance);
    }

    public function test_reused_key_with_different_amount_is_rejected(): void
    {
        $admin = $this->makeUser(true);
        $user = $this->makeUser();
        $this->actingAs($admin);
        $request = $this->payload('credit', 300, 'اصلاح موجودی');

        $this->post(route('admin.users.wallet.adjust', $user), $request)->assertRedirect();
        $this->post(route('admin.users.wallet.adjust', $user), [...$request, 'amount' => 400])
            ->assertSessionHasErrors('idempotency_key');

        $this->assertDatabaseCount('wallet_transactions', 1);
        $this->assertSame(300, Wallet::query()->where('user_id', $user->id)->value('balance'));
    }

    public function test_insufficient_or_invalid_adjustment_does_not_change_the_wallet(): void
    {
        $admin = $this->makeUser(true);
        $user = $this->makeUser();
        $this->actingAs($admin);

        $this->post(route('admin.users.wallet.adjust', $user), $this->payload('credit', 0, 'نامعتبر'))
            ->assertSessionHasErrors('amount');
        $this->post(route('admin.users.wallet.adjust', $user), $this->payload('debit', 10, 'بدون موجودی'))
            ->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('wallet_transactions', 0);
        $this->assertSame(0, Wallet::query()->where('user_id', $user->id)->value('balance'));
    }

    public function test_admin_adjustment_can_debit_blocked_wallet_without_enabling_user_debits(): void
    {
        $admin = $this->makeUser(true);
        $user = $this->makeUser();
        $wallet = Wallet::query()->create(['user_id' => $user->id, 'status' => WalletStatusEnum::BLOCKED]);
        $wallet->forceFill(['balance' => 500, 'withdrawable_balance' => 500])->save();
        $this->actingAs($admin);

        $this->post(route('admin.users.wallet.adjust', $user), $this->payload('debit', 100, 'اصلاح مسدودی'))
            ->assertRedirect();
        $this->assertSame(400, $wallet->fresh()->balance);

        $this->expectException(WalletUnavailableException::class);
        app(DebitWalletAction::class)->execute(new DebitWalletDTO(
            wallet: $wallet,
            type: WalletTransactionTypeEnum::ORDER_PAYMENT,
            amount: 100,
            idempotencyKey: 'test:blocked-user-debit',
            actor: $user,
        ));
    }

    public function test_user_without_wallet_adjust_permission_is_denied(): void
    {
        $admin = $this->makeUser(false);
        $user = $this->makeUser();
        $this->actingAs($admin);

        $this->get(route('admin.users.wallet.show', $user))->assertForbidden();
        $this->post(route('admin.users.wallet.adjust', $user), $this->payload('credit', 100, 'غیرمجاز'))
            ->assertForbidden();
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_user_edit_permission_alone_does_not_allow_wallet_adjustment(): void
    {
        $editor = $this->makeUser();
        $editor->role->update(['is_default' => false]);
        $user = $this->makeUser();
        $permissionId = \Illuminate\Support\Facades\DB::table('permissions')->insertGetId([
            'group' => 'user',
            'ability' => 'edit',
            'label' => 'ویرایش کاربر',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\DB::table('permission_role')->insert([
            'permission_id' => $permissionId,
            'role_id' => $editor->role_id,
        ]);
        $this->actingAs($editor);

        $this->assertTrue($editor->can('user.edit'));
        $this->post(route('admin.users.wallet.adjust', $user), $this->payload('credit', 100, 'غیرمجاز'))
            ->assertForbidden();
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    private function makeUser(bool $superAdmin = false): User
    {
        $roleId = \Illuminate\Support\Facades\DB::table('roles')->insertGetId([
            'title' => $superAdmin ? 'مدیر کل' : 'کاربر',
            'is_default' => !$superAdmin,
            'is_super_admin' => $superAdmin,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->create([
            'username' => (string) Str::ulid(),
            'first_name' => 'کاربر',
            'last_name' => 'آزمایشی',
            'password' => 'password',
            'status' => UserStatusEnum::ACTIVE,
            'role_id' => $roleId,
        ]);
    }

    private function payload(string $direction, int $amount, string $note, string $withdrawable = '0'): array
    {
        return [
            'direction' => $direction,
            'amount' => $amount,
            'withdrawable' => $withdrawable,
            'note' => $note,
            'idempotency_key' => (string) Str::uuid(),
        ];
    }
}
