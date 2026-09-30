<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Financial\Actions\Wallet\CreditWalletAction;
use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Application\Financial\DTOs\CreditWalletDTO;
use App\Domain\Commerce\Enums\OrderRefundMethodEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderRefund;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Identity\Models\User;
use DomainException;

class RecordOrderRefundAction
{
    public function __construct(
        private readonly GetOrCreateWalletAction $getOrCreateWallet,
        private readonly CreditWalletAction $creditWallet,
    ) {
    }

    public function execute(
        Order $order,
        OrderRefundMethodEnum $method,
        ?User $admin,
    ): OrderRefund {
        if ($order->refund()->exists()) {
            throw new DomainException('This order has already been refunded.');
        }

        $amount = (int) $order->getPayableAmount();
        if ($amount <= 0) {
            throw new DomainException('This order has no refundable amount.');
        }

        $walletTransaction = null;
        if ($method === OrderRefundMethodEnum::WALLET) {
            $user = User::query()->withTrashed()->findOrFail($order->user_id);
            $wallet = $this->getOrCreateWallet->execute($user);
            if (!$wallet->allowsUserTransactions()) {
                throw new DomainException('The user wallet is unavailable.');
            }

            $walletTransaction = $this->creditWallet->execute(new CreditWalletDTO(
                wallet: $wallet,
                type: WalletTransactionTypeEnum::ORDER_REFUND,
                amount: $amount,
                idempotencyKey: 'order-refund:' . $order->getKey(),
                withdrawableAmount: 0,
                actor: $admin,
                reference: $order,
                description: 'استرداد سفارش ' . $order->order_number . ' به کیف پول',
                metadata: [
                    'order_id' => $order->getKey(),
                    'order_number' => $order->order_number,
                    'refund_method' => $method->value,
                ],
            ));
        }

        return OrderRefund::query()->create([
            'order_id' => $order->getKey(),
            'method' => $method,
            'amount' => $amount,
            'wallet_transaction_id' => $walletTransaction?->getKey(),
            'processed_by_user_id' => $admin?->getKey(),
        ]);
    }
}
