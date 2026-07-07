@php
    use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
    use App\Domain\Support\Enums\TicketStatusEnum;

    $status = $ticket->status instanceof TicketStatusEnum
        ? $ticket->status
        : TicketStatusEnum::tryFrom((string) $ticket->status);

    $rowClass = match ($status) {
        TicketStatusEnum::OPEN => '!bg-amber-50 !border-b-amber-200/70',
        TicketStatusEnum::ANSWERED => '!bg-emerald-50 !border-b-emerald-200/70',
        TicketStatusEnum::CLOSED => '!bg-slate-50 !border-b-slate-200',
        default => '',
    };

    $statusLabel = $status?->label() ?? 'نامشخص';

    $statusBadgeClass = match ($status) {
        TicketStatusEnum::OPEN => 'border-amber-200 bg-amber-100 text-amber-700',
        TicketStatusEnum::ANSWERED => 'border-emerald-200 bg-emerald-100 text-emerald-700',
        TicketStatusEnum::CLOSED => 'border-slate-200 bg-slate-100 text-slate-700',
        default => 'border-slate-200 bg-slate-100 text-slate-700',
    };

    $latestMessage = $ticket->latestVisibleMessage;

    $latestSenderType = $latestMessage?->sender_type instanceof TicketMessageSenderTypeEnum
        ? $latestMessage?->sender_type
        : TicketMessageSenderTypeEnum::tryFrom((string) $latestMessage?->sender_type);

    $latestSenderLabel = match ($latestSenderType) {
        TicketMessageSenderTypeEnum::USER => 'کاربر',
        TicketMessageSenderTypeEnum::SUPPORT => 'پشتیبانی',
        TicketMessageSenderTypeEnum::SYSTEM => 'سیستم',
        default => '—',
    };
@endphp

<x-admin.list.tr
    data-ticket-row="{{ $ticket->id }}"
    :class="$rowClass"
    ::class="{
        'bg-slate-100/90 ring-1 ring-inset ring-slate-200': isSelected({{ $ticket->id }})
    }"
>
    <x-admin.list.td align="center">
        <x-admin.list.bulk-checkbox
            name="ids[]"
            :value="$ticket->id"
            x-bind:checked="isSelected({{ $ticket->id }})"
            x-on:change="toggleSelection({{ $ticket->id }})"
        />
    </x-admin.list.td>

    <x-admin.list.td>
        <div class="min-w-[220px] space-y-2">
            <div class="text-sm font-bold text-slate-800">
                {{ $ticket->user->display_name }}
            </div>

            <div class="space-y-1 text-xs text-slate-500">
                <div>#{{ $ticket->number }}</div>

                @if($ticket->user->mobile)
                    <div dir="ltr">{{ $ticket->user->mobile }}</div>
                @endif
            </div>
        </div>
    </x-admin.list.td>

    <x-admin.list.td>
        <div class="min-w-[360px]">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-xl border px-2.5 py-1 text-[11px] font-bold {{ $statusBadgeClass }}">
                    {{ $statusLabel }}
                </span>

                <span
                    class="inline-flex rounded-xl border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-bold text-slate-500">
                    {{ number_format((int) ($ticket->visible_messages_count ?? 0)) }} پیام
                </span>
            </div>

            <a
                href="{{ route('admin.tickets.show', $ticket) }}"
                class="line-clamp-1 text-sm font-black text-slate-900 transition hover:text-brand-600"
            >
                {{ $ticket->title }}
            </a>

            <div class="mt-2 line-clamp-2 text-sm leading-7 text-slate-700">
                @if($latestMessage)
                    <span class="font-bold text-slate-500">{{ $latestSenderLabel }}:</span>
                    {{ $latestMessage->body }}
                @else
                    هنوز پیامی ثبت نشده است.
                @endif
            </div>

            <x-admin.list.row-actions class="mt-4">
                <x-admin.list.action
                    tag="a"
                    href="{{ route('admin.tickets.show', $ticket) }}"
                    variant="primary"
                >
                    مشاهده و پاسخ
                </x-admin.list.action>

                @if($status === TicketStatusEnum::CLOSED)
                    <span class="text-slate-300">|</span>

                    <form method="POST" action="{{ route('admin.tickets.reopen', $ticket) }}" class="inline">
                        @csrf

                        <x-admin.list.action tag="button" type="submit">
                            باز کردن مجدد
                        </x-admin.list.action>
                    </form>
                @else
                    <span class="text-slate-300">|</span>

                    <form
                        method="POST"
                        action="{{ route('admin.tickets.close', $ticket) }}"
                        class="inline"
                    >
                        @csrf

                        <x-admin.list.action tag="button" type="submit" variant="danger">
                            بستن
                        </x-admin.list.action>
                    </form>
                @endif
            </x-admin.list.row-actions>
        </div>
    </x-admin.list.td>

    <x-admin.list.td>
        <div class="min-w-[240px] space-y-2">
            @if($ticket->department)
                <span
                    class="inline-flex rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-bold text-slate-700">
                    {{ $ticket->department->title }}
                </span>
            @else
                <span class="text-xs text-slate-400">بدون دپارتمان</span>
            @endif

            <div>
                @if($ticket->product)
                    <a href="{{ $ticket->product->url }}" target="_blank"
                       class="line-clamp-2 text-sm font-bold leading-7 text-slate-700">
                        {{ $ticket->product->title }}
                    </a>
                @else
                    <span class="text-xs text-slate-400">بدون محصول مرتبط</span>
                @endif
            </div>
        </div>
    </x-admin.list.td>

    <x-admin.list.td muted>
        <div class="text-xs leading-6">
            @if($ticket->last_message_at)
                <div>{{ jalali_date($ticket->last_message_at, 'Y/m/d') }}</div>
                <div>{{ jalali_date($ticket->last_message_at, 'H:i') }}</div>
            @else
                <div>—</div>
            @endif
        </div>
    </x-admin.list.td>

    <x-admin.list.td muted>
        <div class="text-xs leading-6">
            <div>{{ jalali_date($ticket->created_at, 'Y/m/d') }}</div>
            <div>{{ jalali_date($ticket->created_at, 'H:i') }}</div>
        </div>
    </x-admin.list.td>
</x-admin.list.tr>
