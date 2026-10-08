@extends('admin.layout')

@section('title', 'مشاهده تیکت')

@section('content')
    @php
        use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
        use App\Domain\Support\Enums\TicketStatusEnum;

        $status = $ticket->status instanceof TicketStatusEnum
            ? $ticket->status
            : TicketStatusEnum::tryFrom((string) $ticket->status);

        $isClosed = $status === TicketStatusEnum::CLOSED;

        $statusBadgeClass = match ($status) {
            TicketStatusEnum::OPEN => 'border-amber-200 bg-amber-50 text-amber-700',
            TicketStatusEnum::ANSWERED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            TicketStatusEnum::CLOSED => 'border-slate-200 bg-slate-100 text-slate-700',
            default => 'border-slate-200 bg-slate-100 text-slate-700',
        };
    @endphp

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <section
            x-data="{
                scrollToBottom() {
                    const box = this.$refs.messagesBox;

                    if (! box) {
                        return;
                    }

                    box.scrollTop = box.scrollHeight;
                }
            }"
            x-init="$nextTick(() => scrollToBottom())"
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="border-b border-slate-100 bg-white/90 px-5 py-4">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-xl border px-2.5 py-1 text-xs font-black {{ $statusBadgeClass }}">
                                {{ $status?->label() ?? 'نامشخص' }}
                            </span>

                            <span
                                class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-black text-slate-600">
                                تیکت #{{ $ticket->number }}
                            </span>

                            @if($ticket->department)
                                <span
                                    class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-black text-slate-600">
                                    {{ $ticket->department->title }}
                                </span>
                            @endif
                        </div>

                        <h1 class="line-clamp-2 text-lg font-black text-slate-900">
                            {{ $ticket->title }}
                        </h1>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <x-admin.form.button
                            tag="a"
                            href="{{ route('admin.tickets.index') }}"
                            variant="secondary"
                        >
                            بازگشت
                        </x-admin.form.button>

                        @if($isClosed)
                            <form method="POST" action="{{ route('admin.tickets.reopen', $ticket) }}">
                                @csrf

                                <x-admin.form.button type="submit" variant="solid">
                                    باز کردن مجدد
                                </x-admin.form.button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.tickets.close', $ticket) }}">
                                @csrf

                                <x-admin.form.button type="submit" variant="secondary">
                                    بستن تیکت
                                </x-admin.form.button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="relative bg-slate-50/70">
                <div
                    x-ref="messagesBox"
                    class="h-[620px] overflow-y-auto scroll-smooth px-4 py-5"
                >
                    @foreach($messages as $message)
                        @php
                            $senderType = $message->sender_type instanceof TicketMessageSenderTypeEnum
                                ? $message->sender_type
                                : TicketMessageSenderTypeEnum::tryFrom((string) $message->sender_type);

                            $isUserMessage = $senderType === TicketMessageSenderTypeEnum::USER;
                            $isSupportMessage = $senderType === TicketMessageSenderTypeEnum::SUPPORT;
                        @endphp

                        @if($isUserMessage)
                            <div class="mb-4 flex justify-end">
                                <div class="max-w-[82%]">
                                    <div class="mb-1 text-right text-xs font-bold text-slate-500">
                                        {{ $ticket->user->display_name }}
                                    </div>

                                    <div
                                        class="rounded-[1.35rem] rounded-bl-md border border-slate-200 bg-white px-4 py-3 text-sm font-bold leading-7 text-slate-700 shadow-sm">
                                        <div class="whitespace-pre-line break-words">{{ $message->body }}</div>

                                        <x-ticket.attachments :message="$message" />
                                    </div>

                                    <div class="mt-1 px-1 text-left text-xs font-bold text-slate-400">
                                        {{ jalali_date($message->created_at, 'Y/m/d H:i') }}
                                    </div>
                                </div>
                            </div>
                        @elseif($isSupportMessage)
                            <div class="mb-4 flex justify-start">
                                <div class="max-w-[82%]">
                                    <div class="mb-1 text-right text-xs font-bold text-brand-600">
                                        {{ $message->user?->name ?? 'پشتیبانی' }}
                                    </div>

                                    <div
                                        class="rounded-[1.35rem] rounded-br-md bg-brand-600 px-4 py-3 text-sm font-bold leading-7 text-white shadow-lg shadow-brand-500/15">
                                        <div class="whitespace-pre-line break-words">{{ $message->body }}</div>

                                        <x-ticket.attachments :message="$message" tone="dark" />
                                    </div>

                                    <div class="mt-1 px-1 text-right text-xs font-bold text-slate-400">
                                        {{ jalali_date($message->created_at, 'Y/m/d H:i') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="my-4 flex justify-center">
                                <div
                                    class="max-w-[80%] rounded-2xl border border-slate-200 bg-white px-4 py-2 text-center text-xs font-bold leading-6 text-slate-500">
                                    {{ $message->body }}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="border-t border-slate-100 bg-white p-4">
                @if($isClosed)
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-bold leading-7 text-slate-600">
                        این تیکت بسته شده است. برای ارسال پاسخ، ابتدا تیکت را مجدداً باز کنید.
                    </div>
                @else
                    <form
                        method="POST"
                        action="{{ route('admin.tickets.reply', $ticket) }}"
                        enctype="multipart/form-data"
                        class="space-y-3"
                    >
                        @csrf

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                پاسخ پشتیبانی
                            </label>

                            <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-2 transition focus-within:border-brand-300 focus-within:bg-white focus-within:ring-4 focus-within:ring-brand-100/60">
                                <x-front.ticket-image-upload
                                    mode="composer"
                                    layout="stacked-actions"
                                    :dark-mode="false"
                                />

                                <textarea
                                    name="message"
                                    rows="3"
                                    class="col-start-1 row-span-2 row-start-1 min-h-[6.5rem] min-w-0 resize-none border-0 bg-transparent px-2 py-2 text-sm font-bold leading-7 text-slate-700 outline-none placeholder:text-slate-400 focus:ring-0"
                                    placeholder="پاسخ خود را بنویسید..."
                                >{{ old('message') }}</textarea>

                                <button
                                    type="submit"
                                    class="col-start-2 row-start-2 grid h-12 w-12 shrink-0 cursor-pointer place-items-center rounded-2xl bg-brand-600 text-white shadow-sm shadow-brand-500/20 transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-100"
                                    aria-label="ارسال پاسخ"
                                    title="ارسال پاسخ"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M21 12 3 20l3.2-8L3 4l18 8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                        <path d="M6.5 12H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>

                            @error('message')
                            <div class="mt-2 text-sm font-bold text-rose-600">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>
                    </form>
                @endif
            </div>
        </section>

        <aside class="space-y-4">
            <x-admin.metabox :collapsible="false" title="اطلاعات تیکت">
                <div class="space-y-4 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <span class="font-bold text-slate-500">شماره</span>
                        <span class="font-black text-slate-800">#{{ $ticket->number }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="font-bold text-slate-500">وضعیت</span>
                        <span class="rounded-xl border px-2.5 py-1 text-xs font-black {{ $statusBadgeClass }}">
                            {{ $status?->label() ?? 'نامشخص' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="font-bold text-slate-500">تاریخ ثبت</span>
                        <span
                            class="font-black text-slate-800">{{ jalali_date($ticket->created_at, 'Y/m/d H:i') }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="font-bold text-slate-500">آخرین پیام</span>
                        <span class="font-black text-slate-800">
                            {{ $ticket->last_message_at ? jalali_date($ticket->last_message_at, 'Y/m/d H:i') : '—' }}
                        </span>
                    </div>

                    @if($ticket->closed_at)
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-bold text-slate-500">تاریخ بسته شدن</span>
                            <span
                                class="font-black text-slate-800">{{ jalali_date($ticket->closed_at, 'Y/m/d H:i') }}</span>
                        </div>
                    @endif
                </div>
            </x-admin.metabox>

            <x-admin.metabox :collapsible="false" title="کاربر">
                <div class="space-y-3">
                    <div class="text-sm font-black text-slate-800">
                        {{ $ticket->user->display_name }}
                    </div>

                    @if($ticket->user->mobile)
                        <div class="text-xs font-bold text-slate-500" dir="ltr">
                            {{ $ticket->user->mobile }}
                        </div>
                    @endif

                    @if($ticket->user->email)
                        <div class="text-xs font-bold text-slate-500" dir="ltr">
                            {{ $ticket->user->email }}
                        </div>
                    @endif
                </div>
            </x-admin.metabox>

            <x-admin.metabox :collapsible="false" title="دپارتمان و محصول">
                @php
                    $departmentRequiresProductMap = $departments
                        ->mapWithKeys(fn ($department) => [
                            (string) $department->id => (bool) $department->requires_product,
                        ])
                        ->all();
                @endphp

                <form
                    method="POST"
                    action="{{ route('admin.tickets.department.update', $ticket) }}"
                    class="space-y-4"
                    x-data="{
                        departmentId: @js((string) old('department_id', $ticket->department_id)),
                        productId: @js((string) old('product_id', $ticket->product_id)),
                        requiresProductMap: @js($departmentRequiresProductMap),

                        get requiresProduct() {
                            return this.requiresProductMap[this.departmentId] === true;
                        }
                    }"
                    x-effect="
                        if (! requiresProduct) {
                            productId = '';
                        }
                    "
                >
                    @csrf
                    @method('PATCH')

                    <div>
                        <label
                            for="department_id"
                            class="mb-2 block text-xs font-bold text-slate-500"
                        >
                            دپارتمان
                        </label>

                        <select
                            id="department_id"
                            name="department_id"
                            x-model="departmentId"
                            required
                            class="block h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100/60"
                        >
                            <option value="">
                                انتخاب دپارتمان
                            </option>

                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">
                                    {{ $department->title }}
                                </option>
                            @endforeach
                        </select>

                        @error('department_id')
                        <div class="mt-2 text-xs font-bold text-rose-600">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div
                        x-cloak
                        x-show="requiresProduct"
                        x-transition.opacity
                    >
                        <label
                            for="product_id"
                            class="mb-2 block text-xs font-bold text-slate-500"
                        >
                            محصول مرتبط
                            <span class="text-rose-500">*</span>
                        </label>

                        <select
                            id="product_id"
                            name="product_id"
                            x-model="productId"
                            :disabled="! requiresProduct"
                            :required="requiresProduct"
                            class="block h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100/60"
                        >
                            <option value="">
                                انتخاب محصول
                            </option>

                            @foreach($ticketProducts as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->title }}
                                </option>
                            @endforeach
                        </select>

                        <div class="mt-2 text-xs font-bold leading-6 text-slate-400">
                            دپارتمان انتخاب‌شده نیازمند تعیین محصول مرتبط است.
                        </div>

                        @error('product_id')
                        <div class="mt-2 text-xs font-bold text-rose-600">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    @if($ticket->product)
                        <div
                            x-show="! requiresProduct"
                            class="rounded-xl border border-slate-200 bg-slate-50 p-3"
                        >
                            <div class="mb-1 text-xs font-bold text-slate-500">
                                محصول فعلی
                            </div>

                            <a
                                href="{{ $ticket->product->url }}"
                                target="_blank"
                                class="text-sm font-black leading-7 text-slate-800 transition hover:text-brand-600"
                            >
                                {{ $ticket->product->title }}
                            </a>
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <x-admin.form.button
                            type="submit"
                            variant="soft"
                            size="sm"
                        >
                            ذخیره تغییرات
                        </x-admin.form.button>
                    </div>
                </form>
            </x-admin.metabox>
        </aside>
    </div>

    <x-ticket.image-lightbox />
@endsection
