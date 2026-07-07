<?php

namespace App\Application\Support\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Enums\TicketStatusEnum;
use App\Domain\Support\Models\Ticket;
use App\Domain\Support\Models\TicketDepartment;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTicketAction
{
    public function __construct(
        private readonly CreateTicketMessageAction   $createTicketMessageAction,
        private readonly EnsureCanCreateTicketAction $ensureCanCreateTicketAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(
        User   $user,
        int    $departmentId,
        ?int   $productId,
        string $title,
        string $message,
    ): Ticket
    {
        return DB::transaction(function () use ($user, $departmentId, $productId, $title, $message) {
            $department = TicketDepartment::query()
                ->active()
                ->find($departmentId);

            if (!$department) {
                throw new DomainException('دپارتمان انتخاب‌شده معتبر نیست.');
            }

            $product = null;

            if ($department->requiresProduct()) {
                if (!$productId) {
                    throw new DomainException('برای این دپارتمان انتخاب محصول الزامی است.');
                }

                $product = Product::query()
                    ->where('status', 'active')
                    ->find($productId);

                if (!$product) {
                    throw new DomainException('محصول انتخاب‌شده معتبر نیست.');
                }
            }

            $this->ensureCanCreateTicketAction->execute($user);

            $ticket = Ticket::query()->create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'product_id' => $department->requiresProduct() ? $product?->id : null,
                'title' => trim($title),
                'status' => TicketStatusEnum::OPEN,
                'last_message_at' => null,
                'last_user_message_at' => null,
                'last_support_message_at' => null,
            ]);

            $ticket->update([
                'number' => $ticket->id + 1000,
            ]);

            $this->createTicketMessageAction->execute(
                ticket: $ticket,
                sender: $user,
                senderType: TicketMessageSenderTypeEnum::USER,
                body: $message,
                isInternalNote: false,
                enforceUserReplyLimits: false,
            );

            return $ticket->refresh()->load([
                'user',
                'department',
                'product',
                'latestMessage',
            ]);
        });
    }
}
