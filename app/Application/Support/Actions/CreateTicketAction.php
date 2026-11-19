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
        array  $attachments = [],
    ): Ticket
    {
        return DB::transaction(function () use ($user, $departmentId, $productId, $title, $message, $attachments) {
            $department = TicketDepartment::query()
                ->active()
                ->find($departmentId);

            if (!$department) {
                throw new DomainException('دپارتمان انتخاب‌شده معتبر نیست.');
            }

            // اعضای VIP فعال می‌توانند بدون انتخاب محصول تیکت ثبت کنند
            $vipSkipsProduct = $user->hasActiveVipSubscription();

            if (!$vipSkipsProduct && $department->requiresProduct() && !$productId) {
                throw new DomainException('برای این دپارتمان انتخاب محصول الزامی است.');
            }

            // محصول ارسالی (در صورت وجود) باید فعال باشد؛ دپارتمان بدون الزامِ
            // محصول، مقدار آن را نادیده می‌گیرد.
            $ticketProductId = null;

            if ($productId && ($department->requiresProduct() || $vipSkipsProduct)) {
                $product = Product::query()
                    ->where('status', 'active')
                    ->find($productId);

                if (!$product) {
                    throw new DomainException('محصول انتخاب‌شده معتبر نیست.');
                }

                $ticketProductId = $product->id;
            }

            $this->ensureCanCreateTicketAction->execute($user);

            $ticket = Ticket::query()->create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'product_id' => $ticketProductId,
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
                enforceUserReplyLimits: false,
                attachments: $attachments,
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
