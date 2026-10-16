<?php

namespace App\Application\Notification\Jobs;

use App\Application\Notification\Support\CustomerSmsPayloadFactory;
use App\Domain\Commerce\Models\Order;
use App\Domain\Notification\Enums\CustomerSmsType;
use App\Domain\Support\Models\TicketMessage;
use App\Infrastructure\Services\Setting\Groups\NotificationsSettings;
use App\Infrastructure\Services\Sms\SmsService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;

final class SendCustomerSmsJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public int $uniqueFor = 600;

    public function __construct(
        public readonly CustomerSmsType $type,
        public readonly int $subjectId,
    ) {}

    public function uniqueId(): string
    {
        return 'customer-sms:'.$this->type->value.':'.$this->subjectId;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->uniqueId()))->releaseAfter(10)->expireAfter(60)];
    }

    public function handle(NotificationsSettings $settings, CustomerSmsPayloadFactory $factory): void
    {
        if (! $settings->get($this->type->settingKey(), false)) {
            return;
        }

        if ($this->type === CustomerSmsType::TICKET_REPLY) {
            $subject = TicketMessage::query()->with('ticket.user')->find($this->subjectId);
            if (! $subject || ! $subject->isFromSupport() || ! $subject->ticket
                || $subject->user_id === $subject->ticket->user_id) {
                return;
            }
            $user = $subject->ticket->user;
            $number = (string) $subject->ticket->number;
            $numberKey = 'ticket_number';
        } else {
            $subject = Order::query()->with('user')->find($this->subjectId);
            if (! $subject || ! $subject->isPaid()) {
                return;
            }
            $user = $subject->user;
            $number = (string) $subject->order_number;
            $numberKey = 'order_number';
        }

        if ($subject->customer_sms_sent_at || ! $user || blank($user->mobile)) {
            return;
        }

        $payload = $factory->make($this->type, [
            'name' => trim((string) ($user->display_name ?: $user->full_name ?: $user->username)) ?: 'کاربر عزیز',
            $numberKey => $number,
        ]);
        $messageId = app(SmsService::class)->sendPattern($user->mobile, $payload['pattern_id'], $payload['values']);

        // Sending metadata must not change the business record's updated_at or trigger its observers.
        DB::table($subject->getTable())->where('id', $subject->getKey())->update([
            'customer_sms_sent_at' => now(),
            'customer_sms_message_id' => $messageId,
        ]);
    }

    public function backoff(): array
    {
        return [15, 60, 180];
    }
}
