<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderDescription;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AddOrderDescriptionAction
{
    public function execute(Order $order, string $description, ?User $admin = null): OrderDescription
    {
        $description = trim($description);
        if ($description === '') {
            throw new InvalidArgumentException('Order description cannot be empty.');
        }

        return DB::transaction(function () use ($order, $description, $admin): OrderDescription {
            $order = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            return $order->descriptions()->create([
                'body' => $description,
                'created_by_user_id' => $admin?->getKey(),
            ]);
        });
    }
}
