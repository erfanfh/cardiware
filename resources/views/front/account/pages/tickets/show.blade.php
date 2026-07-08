@extends('front.account.layout')

@section('page-content')
    @php
        use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
        use App\Domain\Support\Enums\TicketStatusEnum;
        use Illuminate\Support\Facades\Route;
        use Morilog\Jalali\Jalalian;

        $ticketStatus = $ticket->status;

        $isClosed = $ticketStatus->isClosed();

        $statusLabel = $ticketStatus->label();

        $statusClasses = $isClosed
            ? 'border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
            : match ($ticketStatus) {
                TicketStatusEnum::ANSWERED => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300',
                default => 'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300',
            };

        $messages = $ticket->messages()
            ->with('user')
            ->oldest('created_at')
            ->oldest('id')
            ->get();

        $replyAction = route('account.tickets.messages.store', $ticket);

        $backUrl = route('account.tickets');

        $lastDate = null;
    @endphp

    <section
        x-data="{
            reply: @js(old('message', '')),

            scrollToBottom() {
                const box = this.$refs.messagesBox;

                if (! box) {
                    return;
                }

                box.scrollTop = box.scrollHeight;
            },

            resizeTextarea() {
                const textarea = this.$refs.replyTextarea;

                if (! textarea) {
                    return;
                }

                textarea.style.height = 'auto';
                textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
            },

            submitReply() {
                if (! this.reply.trim()) {
                    return;
                }

                this.$refs.replyForm?.requestSubmit();
            }
        }"
        x-init="
            $nextTick(() => {
                scrollToBottom();
                resizeTextarea();
            });
        "
        class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20"
    >
        {{-- Header --}}
        <div
            class="sticky top-0 z-20 border-b border-slate-100 bg-white/90 px-4 py-4 backdrop-blur-xl sm:px-5 dark:border-slate-800 dark:bg-slate-900/90">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <a
                        href="{{ $backUrl }}"
                        class="max-sm:hidden grid h-10 w-10 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-white hover:text-brand-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-brand-300"
                        aria-label="بازگشت"
                    >
                        <svg class="h-5 w-5 rtl:rotate-180" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M15 18 9 12l6-6"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </a>

                    <div
                        class="max-sm:hidden grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M8 10h8M8 14h5M21 12c0 4.418-4.03 8-9 8a10.4 10.4 0 0 1-4.064-.816L3 20l1.26-3.36A7.57 7.57 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="truncate text-sm font-black text-slate-900 sm:text-base dark:text-white">
                                {{ $ticket->title }}
                            </h1>

                            <span
                                class="inline-flex shrink-0 items-center rounded-full border px-2.5 py-1 text-[11px] font-black {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div
                            class="mt-1 flex flex-wrap items-center gap-2 text-[11px] font-bold text-slate-400 dark:text-slate-500">
                            <span dir="ltr">#{{ $ticket->number }}</span>

                            <span class="h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>

                            <span>
                                آخرین به‌روزرسانی:
                                @if($ticket->last_message_at)
                                    {{ jalali_date($ticket->last_message_at, 'Y/m/d - H:i') }}
                                @else
                                    نامشخص
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($isClosed)
                        <span
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-xs font-extrabold text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                            <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                            تیکت بسته شده
                        </span>
                    @else
                        <span
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3 text-xs font-extrabold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                            گفتگو فعال است
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Chat Area --}}
        <div class="relative">
            {{-- Soft background pattern --}}
            <div class="pointer-events-none absolute inset-0 opacity-70 dark:opacity-40">
                <div
                    class="absolute -right-20 top-10 h-48 w-48 rounded-full bg-brand-100 blur-3xl dark:bg-brand-500/10"></div>
                <div
                    class="absolute -left-20 bottom-10 h-56 w-56 rounded-full bg-sky-100 blur-3xl dark:bg-sky-500/10"></div>
            </div>

            <div
                x-ref="messagesBox"
                class="relative h-auto max-h-[520px] md:max-h-[700px] overflow-y-auto scroll-smooth px-3 py-5 sm:px-5"
            >
                @if(session('ticket_message_created'))
                    <div class="mb-4 flex justify-center">
                        <div
                            class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-extrabold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="m5 13 4 4L19 7"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            پیام شما ارسال شد
                        </div>
                    </div>
                @endif

                @if($errors->replyTicket->any())
                    <div
                        class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-500/20 dark:bg-rose-500/10">
                        <div class="text-xs font-black text-rose-700 dark:text-rose-300">
                            ارسال پیام انجام نشد
                        </div>

                        <div class="mt-2 space-y-1">
                            @foreach($errors->replyTicket->all() as $error)
                                <div class="text-xs font-bold leading-6 text-rose-600 dark:text-rose-300">
                                    {{ $error }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @forelse($messages as $message)
                    @php
                        $messageSenderType = $message->sender_type;

                        $currentDate = $message->created_at?->toDateString();

                        $timeLabel = jalali_date($message->created_at, 'H:i');
                        $dateLabel = jalali_date($message->created_at, 'Y/m/d');
                    @endphp

                    @if($currentDate && $lastDate !== $currentDate)
                        <div class="my-5 flex items-center justify-center">
                            <span
                                class="rounded-full border border-slate-200 bg-white/80 px-3 py-1 text-[11px] font-black text-slate-400 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-500">
                                {{ $dateLabel }}
                            </span>
                        </div>

                        @php
                            $lastDate = $currentDate;
                        @endphp
                    @endif

                    @if($messageSenderType->isSystem())
                        <div class="my-4 flex justify-center">
                            <div
                                class="max-w-[90%] rounded-2xl border border-slate-200 bg-white/80 px-4 py-2 text-center text-xs font-bold leading-6 text-slate-500 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-400">
                                {{ $message->body }}
                            </div>
                        </div>
                    @elseif($messageSenderType->isUser())
                        {{-- User Message --}}
                        <div class="mb-4 flex justify-start">
                            <div class="max-w-[88%] sm:max-w-[74%]">
                                <div
                                    class="relative rounded-[1.35rem] rounded-br-md bg-brand-600 px-4 py-3 text-sm font-bold leading-7 text-white shadow-lg shadow-brand-500/15 dark:bg-brand-500 dark:shadow-black/10">
                                    <div class="whitespace-pre-line break-words">{{ $message->body }}</div>
                                </div>

                                <div
                                    class="mt-1 flex items-center justify-start gap-1.5 px-1 text-[10px] font-bold text-slate-400 dark:text-slate-500">
                                    <span>{{ $timeLabel }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Support Message --}}
                        <div class="mb-4 flex justify-end">
                            <div class="flex max-w-[88%] items-end gap-2 sm:max-w-[74%]">
                                <div>
                                    <div
                                        class="relative rounded-[1.35rem] rounded-bl-md border border-slate-200 bg-white px-4 py-3 text-sm font-bold leading-7 text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                        <div class="mb-1 text-[11px] font-black text-brand-600 dark:text-brand-300">
                                            پشتیبانی مودینگ وی
                                        </div>

                                        <div class="whitespace-pre-line break-words">{{ $message->body }}</div>
                                    </div>

                                    <div
                                        class="mt-1 flex items-center justify-end gap-1 px-1 text-[10px] font-bold text-slate-400 dark:text-slate-500">
                                        <span>{{ $timeLabel }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="flex h-full min-h-[420px] items-center justify-center">
                        <div class="text-center">
                            <div
                                class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white text-brand-600 shadow-sm ring-1 ring-brand-100 dark:bg-slate-900 dark:text-brand-300 dark:ring-brand-500/20">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M8 10h8M8 14h5M21 12c0 4.418-4.03 8-9 8a10.4 10.4 0 0 1-4.064-.816L3 20l1.26-3.36A7.57 7.57 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <div class="mt-4 text-sm font-black text-slate-900 dark:text-white">
                                هنوز پیامی در این تیکت ثبت نشده است
                            </div>

                            <p class="mx-auto mt-2 max-w-sm text-xs font-medium leading-6 text-slate-500 dark:text-slate-400">
                                پیام‌های شما و تیم پشتیبانی در این بخش نمایش داده می‌شود.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Reply Box --}}
        <div class="border-t border-slate-100 bg-white px-3 py-3 sm:px-5 dark:border-slate-800 dark:bg-slate-900">
            @if($isClosed)
                <div
                    class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-slate-800/70">
                    <div class="flex items-center gap-3">
                        <div
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 [&_svg]:w-5 [&_svg]:h-5">
                            @include('icons.lock-password')
                        </div>

                        <div>
                            <div class="text-sm font-black text-slate-800 dark:text-white">
                                این تیکت بسته شده است
                            </div>

                            <p class="mt-1 text-xs font-medium leading-6 text-slate-500 dark:text-slate-400">
                                امکان ارسال پیام جدید برای این تیکت وجود ندارد. در صورت نیاز، یک تیکت جدید ثبت کنید.
                            </p>
                        </div>
                    </div>

                    <a
                        href="{{ $backUrl }}"
                        class="inline-flex h-11 items-center justify-center rounded-2xl bg-brand-600 px-5 text-sm font-extrabold text-white transition hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-400"
                    >
                        بازگشت به تیکت‌ها
                    </a>
                </div>
            @else
                <form
                    x-ref="replyForm"
                    method="POST"
                    action="{{ route('account.tickets.messages.store', $ticket) }}"
                    class="rounded-3xl border border-slate-200 bg-slate-50 p-2 shadow-inner shadow-slate-200/40 dark:border-slate-700 dark:bg-slate-800/70 dark:shadow-black/10"
                >
                    @csrf

                    <div class="flex items-end gap-2">
                        <div class="flex min-w-0 flex-1">
                            <textarea
                                x-ref="replyTextarea"
                                x-model="reply"
                                x-on:input="resizeTextarea()"
                                x-on:keydown.enter.exact.prevent="submitReply()"
                                name="message"
                                rows="1"
                                placeholder="پیام خود را بنویسید..."
                                class="max-h-40 min-h-[48px] w-full resize-none border-0 bg-transparent px-3 py-2 text-sm font-bold leading-7 text-slate-800 outline-none placeholder:text-slate-400 focus:ring-0 dark:text-white dark:placeholder:text-slate-500"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            :disabled="! reply.trim()"
                            class="group grid h-12 w-12 shrink-0 cursor-pointer place-items-center rounded-2xl bg-brand-600 text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:shadow-none dark:bg-brand-500 dark:hover:bg-brand-400 dark:disabled:bg-slate-700 dark:disabled:text-slate-500"
                            aria-label="ارسال پیام"
                        >
                            <svg class="h-6 w-6 transition group-hover:-translate-x-0.5 group-hover:translate-y-0.5"
                                 viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M21 12 3 20l3.2-8L3 4l18 8Z"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M6.5 12H13"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </section>
@endsection
