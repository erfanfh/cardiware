<?php

namespace App\Livewire\Front\Account;

use App\Application\Support\Actions\GetUserTicketsAction;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Enums\TicketStatusEnum;
use App\Domain\Support\Models\Ticket;
use App\Domain\Support\Models\TicketMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

class MyTicketsSection extends Component
{
    use WithPagination;

    public int $perPage = 6;

    #[Computed]
    public function tickets(): LengthAwarePaginator
    {
        if (!auth()->check()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        return app(GetUserTicketsAction::class)->handle(
            userId: (int)auth()->id(),
            perPage: $this->perPage,
        );
    }

    public function statusLabel(Ticket $ticket): string
    {
        return $ticket->status instanceof TicketStatusEnum
            ? $ticket->status->label()
            : TicketStatusEnum::tryFrom((string)$ticket->status)?->label() ?? 'نامشخص';
    }

    public function statusClass(Ticket $ticket): string
    {
        $status = $ticket->status instanceof TicketStatusEnum
            ? $ticket->status
            : TicketStatusEnum::tryFrom((string)$ticket->status);

        return match ($status) {
            TicketStatusEnum::ANSWERED => '
                border border-emerald-200
                bg-emerald-50
                text-emerald-700
                dark:border-emerald-500/20
                dark:bg-emerald-500/10
                dark:text-emerald-300
            ',

            TicketStatusEnum::CLOSED => '
                border border-slate-200
                bg-slate-100
                text-slate-700
                dark:border-slate-700
                dark:bg-slate-800
                dark:text-slate-300
            ',

            TicketStatusEnum::OPEN => '
                border border-brand-200
                bg-brand-50
                text-brand-700
                dark:border-brand-500/20
                dark:bg-brand-500/10
                dark:text-brand-300
            ',

            default => '
                border border-slate-200
                bg-slate-100
                text-slate-700
                dark:border-slate-700
                dark:bg-slate-800
                dark:text-slate-300
            ',
        };
    }

    public function dateLabel($date): string
    {
        if (!$date) {
            return '—';
        }

        return Jalalian::fromDateTime($date)->format('Y/m/d');
    }

    public function dateTimeLabel($date): string
    {
        if (!$date) {
            return '—';
        }

        return Jalalian::fromDateTime($date)->format('Y/m/d H:i');
    }

    public function latestActivityLabel(Ticket $ticket): string
    {
        return $this->dateTimeLabel($ticket->last_message_at ?? $ticket->created_at);
    }

    public function latestMessagePreview(Ticket $ticket): string
    {
        $message = $ticket->latestVisibleMessage;

        if (!$message) {
            return 'هنوز پیامی برای این تیکت ثبت نشده است.';
        }

        return str($message->body)
            ->squish()
            ->limit(120)
            ->toString();
    }

    public function latestMessageSenderLabel(Ticket $ticket): string
    {
        $message = $ticket->latestVisibleMessage;

        if (!$message) {
            return 'بدون پیام';
        }

        $senderType = $message->sender_type instanceof TicketMessageSenderTypeEnum
            ? $message->sender_type
            : TicketMessageSenderTypeEnum::tryFrom((string)$message->sender_type);

        return match ($senderType) {
            TicketMessageSenderTypeEnum::USER => 'شما',
            TicketMessageSenderTypeEnum::SUPPORT => 'پشتیبانی',
            TicketMessageSenderTypeEnum::SYSTEM => 'سیستم',
            default => 'نامشخص',
        };
    }

    public function latestMessageSenderClass(Ticket $ticket): string
    {
        $message = $ticket->latestVisibleMessage;

        if (!$message) {
            return 'text-slate-400 dark:text-slate-500';
        }

        $senderType = $message->sender_type instanceof TicketMessageSenderTypeEnum
            ? $message->sender_type
            : TicketMessageSenderTypeEnum::tryFrom((string)$message->sender_type);

        return match ($senderType) {
            TicketMessageSenderTypeEnum::SUPPORT => 'text-emerald-700 dark:text-emerald-300',
            TicketMessageSenderTypeEnum::USER => 'text-brand-700 dark:text-brand-300',
            default => 'text-slate-500 dark:text-slate-400',
        };
    }

    public function ticketUrl(Ticket $ticket): string
    {
        return route('account.tickets.show', $ticket);
    }

    public function isAnsweredBySupport(Ticket $ticket): bool
    {
        $message = $ticket->latestVisibleMessage;

        if (!$message instanceof TicketMessage) {
            return false;
        }

        $senderType = $message->sender_type instanceof TicketMessageSenderTypeEnum
            ? $message->sender_type
            : TicketMessageSenderTypeEnum::tryFrom((string)$message->sender_type);

        return $senderType === TicketMessageSenderTypeEnum::SUPPORT;
    }

    public function render(): View
    {
        return view('livewire.front.account.my-tickets-section');
    }
}
