@php
    use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
    use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;

    $isEffective = $winner->isEffectivePrimary();
    $effectivePosition = $winner->effectivePrimaryPosition();
    $replacement = $winner->replacement;
    $statusColor = match($winner->status) {
        DrawWinnerStatusEnum::PENDING => 'yellow',
        DrawWinnerStatusEnum::CONFIRMED => 'green',
        DrawWinnerStatusEnum::DELIVERED => 'brand',
        DrawWinnerStatusEnum::REJECTED => 'red',
        DrawWinnerStatusEnum::EXPIRED => 'red',
        DrawWinnerStatusEnum::REPLACED => 'slate',
    };
@endphp

<div class="rounded-2xl border {{ $winner->promoted_at ? 'border-brand-200 bg-brand-50/30' : 'border-slate-200 bg-white' }} p-4">
    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <div class="text-sm font-black text-slate-900">{{ $winner->user?->full_name ?? 'کاربر حذف شده' }}</div>
                <x-admin.badge :color="$statusColor" size="sm">{{ $winner->status->label() }}</x-admin.badge>

                @if($winner->kind === DrawWinnerKindEnum::PRIMARY)
                    <x-admin.badge color="dark" size="sm">اصلی #{{ $winner->position }}</x-admin.badge>
                @else
                    <x-admin.badge color="slate" size="sm">ذخیره #{{ $winner->position }}</x-admin.badge>
                @endif

                @if($winner->promoted_at)
                    <x-admin.badge color="brand" size="sm">جایگزین جایگاه #{{ $effectivePosition }}</x-admin.badge>
                @endif

                @if($winner->drawRun?->type?->value === 'redraw')
                    <x-admin.badge color="blue" size="sm">Redraw</x-admin.badge>
                @endif
            </div>

            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-500">
                <span>User #{{ $winner->user_id }}</span>
                <span dir="ltr">Ticket: {{ $winner->ticket?->code }}</span>
                <span>Run #{{ $winner->draw_run_id }}</span>
                <span>انتخاب: {{ jalali_date($winner->selected_at, 'Y/m/d H:i') }}</span>
            </div>

            @if($winner->confirmation_deadline_at && $winner->status === DrawWinnerStatusEnum::PENDING && $isEffective)
                <div class="mt-3 rounded-xl {{ $winner->confirmation_deadline_at->isPast() ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700' }} px-3 py-2 text-xs font-bold">
                    مهلت تایید: {{ jalali_date($winner->confirmation_deadline_at, 'Y/m/d H:i') }}
                </div>
            @endif

            @if($winner->rejection_reason)
                <div class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-xs leading-6 text-red-700">
                    <span class="font-black">دلیل رد:</span> {{ $winner->rejection_reason }}
                </div>
            @endif

            @if($winner->claimed_at)
                <div class="mt-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-3 text-xs leading-6 text-sky-800">
                    <div class="font-black">اطلاعات ثبت‌شده توسط برنده</div>
                    <div class="mt-1">{{ $winner->claim_contact_name }} · <span dir="ltr">{{ $winner->claim_mobile }}</span></div>
                    @if($winner->claim_postal_code)<div>کد پستی: <span dir="ltr">{{ $winner->claim_postal_code }}</span></div>@endif
                    <div>{{ $winner->claim_address }}</div>
                    <div class="mt-1 text-[10px] text-sky-600">ثبت: {{ jalali_date($winner->claimed_at, 'Y/m/d H:i') }}</div>
                </div>
            @endif

            @if($isEffective)
                <div class="mt-3 flex flex-wrap gap-2 text-[10px]">
                    @if($winner->winner_sms_sent_at)
                        <span class="rounded-lg bg-emerald-50 px-2 py-1 font-bold text-emerald-700">پیامک برنده ارسال شد · {{ jalali_date($winner->winner_sms_sent_at, 'Y/m/d H:i') }}</span>
                    @elseif($winner->winner_sms_failed_at)
                        <span class="rounded-lg bg-red-50 px-2 py-1 font-bold text-red-700" title="{{ $winner->winner_sms_failure }}">ارسال پیامک ناموفق</span>
                    @else
                        <span class="rounded-lg bg-slate-100 px-2 py-1 font-bold text-slate-500">پیامک برنده ارسال نشده</span>
                    @endif
                    @if($winner->winner_reminder_sms_sent_at)
                        <span class="rounded-lg bg-amber-50 px-2 py-1 font-bold text-amber-700">یادآوری ارسال شد</span>
                    @elseif($winner->winner_reminder_sms_failed_at)
                        <span class="rounded-lg bg-red-50 px-2 py-1 font-bold text-red-700" title="{{ $winner->winner_reminder_sms_failure }}">ارسال یادآوری ناموفق</span>
                    @endif
                </div>
            @endif

            @if($winner->replacedWinner)
                <div class="mt-3 text-xs text-brand-700">
                    جایگزین Winner #{{ $winner->replacedWinner->id }} شده است.
                </div>
            @endif

            @if($replacement)
                <div class="mt-3 rounded-xl bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                    این نتیجه با Winner #{{ $replacement->id }} ({{ $replacement->user?->full_name }}) جایگزین شده است.
                </div>
            @endif

            @if($winner->status === DrawWinnerStatusEnum::DELIVERED)
                <div class="mt-3 rounded-xl bg-brand-50 px-3 py-2 text-xs leading-6 text-brand-700">
                    تحویل: {{ jalali_date($winner->delivered_at, 'Y/m/d H:i') }}
                    @if($winner->delivery_note)<div class="mt-1">{{ $winner->delivery_note }}</div>@endif
                </div>
            @endif
        </div>

        @if($isEffective && !$replacement)
            <div class="w-full shrink-0 space-y-2 lg:w-[310px]">
                @if($winner->status === DrawWinnerStatusEnum::PENDING)
                    @can('raffle-winner.confirm')
                        <x-admin.form method="post" action="{{ route('admin.draw-campaigns.winners.notify', [$drawCampaign, $winner]) }}">
                            <x-admin.form.button type="submit" variant="secondary" class="w-full justify-center">{{ $winner->winner_sms_sent_at ? 'ارسال مجدد پیامک' : 'ارسال پیامک برنده' }}</x-admin.form.button>
                        </x-admin.form>
                    @endcan
                    @can('raffle-winner.confirm')
                        <x-admin.form method="post" action="{{ route('admin.draw-campaigns.winners.confirm', [$drawCampaign, $winner]) }}" class="js-confirm" data-confirm-title="تایید برنده" data-confirm-text="این کاربر به عنوان برنده این جایگاه تایید شود؟">
                            <x-admin.form.button type="submit" variant="solid" class="w-full justify-center">تایید برنده</x-admin.form.button>
                        </x-admin.form>
                    @endcan
                @endif

                @if(in_array($winner->status, [DrawWinnerStatusEnum::PENDING, DrawWinnerStatusEnum::CONFIRMED], true))
                    @can('raffle-winner.reject')
                        <x-admin.form method="post" action="{{ route('admin.draw-campaigns.winners.reject', [$drawCampaign, $winner]) }}" class="space-y-2">
                            <x-admin.form.input name="reason" placeholder="دلیل رد صلاحیت" required/>
                            <x-admin.form.button type="submit" variant="danger" class="w-full justify-center">رد و جایگزینی خودکار</x-admin.form.button>
                        </x-admin.form>
                    @endcan
                @endif

                @if($winner->status === DrawWinnerStatusEnum::CONFIRMED)
                    @can('raffle-winner.deliver')
                        <x-admin.form method="post" action="{{ route('admin.draw-campaigns.winners.deliver', [$drawCampaign, $winner]) }}" class="space-y-2">
                            <x-admin.form.input name="note" placeholder="توضیح / کد پیگیری تحویل"/>
                            <x-admin.form.button type="submit" variant="secondary" class="w-full justify-center">ثبت تحویل جایزه</x-admin.form.button>
                        </x-admin.form>
                    @endcan
                @endif

                @if(in_array($winner->status, [DrawWinnerStatusEnum::REJECTED, DrawWinnerStatusEnum::EXPIRED], true))
                    @can('raffle-winner.promote')
                        <x-admin.form method="post" action="{{ route('admin.draw-campaigns.winners.promote', [$drawCampaign, $winner]) }}">
                            <x-admin.form.button type="submit" variant="secondary" class="w-full justify-center">Promote ذخیره بعدی</x-admin.form.button>
                        </x-admin.form>
                    @endcan

                    @can('raffle-draw.execute')
                        <x-admin.form method="post" action="{{ route('admin.draw-campaigns.winners.redraw', [$drawCampaign, $winner]) }}" class="js-confirm" data-confirm-title="شروع Redraw" data-confirm-text="اگر ذخیره ای باقی نمانده باشد، برای همین جایگاه DrawRun جدید با Seed مستقل ساخته می شود.">
                            <x-admin.form.button type="submit" variant="solid" class="w-full justify-center">شروع Redraw برای این جایگاه</x-admin.form.button>
                        </x-admin.form>
                    @endcan
                @endif
            </div>
        @elseif(!$isEffective && $winner->kind === DrawWinnerKindEnum::RESERVE && !$winner->promoted_at)
            <div class="shrink-0">
                <x-admin.badge color="slate" size="md">در صف ذخیره</x-admin.badge>
            </div>
        @endif
    </div>
</div>
