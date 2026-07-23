<?php

namespace Tests\Feature\Application\Financial\Wallet;

use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Domain\Financial\Enums\WalletStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetOrCreateWalletActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_only_one_wallet_for_a_user(): void
    {
        $user = User::factory()->create();

        $action = app(GetOrCreateWalletAction::class);

        $firstWallet = $action->execute($user);
        $secondWallet = $action->execute($user);

        $this->assertSame(
            $firstWallet->id,
            $secondWallet->id,
        );

        $this->assertDatabaseCount('wallets', 1);

        $this->assertDatabaseHas('wallets', [
            'id' => $firstWallet->id,
            'user_id' => $user->id,
            'status' => WalletStatusEnum::ACTIVE->value,

            'balance' => 0,
            'withdrawable_balance' => 0,

            'held_balance' => 0,
            'held_withdrawable_balance' => 0,
        ]);
    }
}
