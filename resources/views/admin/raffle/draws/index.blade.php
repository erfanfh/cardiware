@extends('admin.layout')

@section('title', 'اجرای قرعه کشی ' . $drawCampaign->title)

@section('content')
    @include('admin.raffle.campaigns.partials.nav')

    @php
        use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
        use App\Domain\Raffle\Enums\DrawRunStatusEnum;
        use App\Domain\Raffle\Enums\DrawWinnerKindEnum;

        $canStart = $drawCampaign->status === DrawCampaignStatusEnum::CLOSED && !$hasInitialRun;
        $drawTimeReached = !$drawCampaign->draw_at || now()->gte($drawCampaign->draw_at);
    @endphp

    <div class="space-y-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-lg font-black text-slate-900">موتور قرعه کشی</h1>
                        <x-admin.badge color="brand" size="md">{{ $drawCampaign->status->label() }}</x-admin.badge>
                    </div>
                    <p class="mt-2 max-w-3xl text-xs leading-7 text-slate-500">
                        پس از شروع، بلیط‌های واجد شرایط قفل می‌شوند و اجرای دوباره پس از خطا نیز با همان اطلاعات اولیه انجام می‌شود.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.show', $drawCampaign) }}" variant="secondary">
                        بازگشت به مدیریت قرعه کشی
                    </x-admin.form.button>

                    @can('raffle-draw.execute')
                        @if($canStart && $drawTimeReached)
                            <x-admin.form
                                action="{{ route('admin.draw-campaigns.draws.store', $drawCampaign) }}"
                                method="post"
                                class="js-confirm"
                                data-confirm-title="شروع قرعه کشی"
                                data-confirm-text="پس از شروع، صدور یا ابطال بلیط ممکن نیست و نتیجه در صف پردازش قرار می‌گیرد. ادامه می‌دهید؟"
                            >
                                <x-admin.form.button type="submit" variant="solid">شروع قرعه‌کشی</x-admin.form.button>
                            </x-admin.form>
                        @endif
                    @endcan
                </div>
            </div>

            @if($canStart && !$drawTimeReached)
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-7 text-amber-800">
                    زمان تعیین‌شده برای قرعه‌کشی هنوز نرسیده است: {{ jalali_date($drawCampaign->draw_at, 'Y/m/d H:i') }}
                </div>
            @elseif($drawCampaign->status === DrawCampaignStatusEnum::OPEN)
                <div class="mt-4 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs leading-7 text-blue-800">
                    ابتدا دریافت بلیط را ببندید؛ قرعه‌کشی فقط در وضعیت «بسته شده» قابل اجراست.
                </div>
            @elseif($drawCampaign->status === DrawCampaignStatusEnum::DRAWING)
                <div class="mt-4 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-xs leading-7 text-brand-800">
                    بلیط‌های واجد شرایط قفل شده‌اند و تا پایان اجرای جاری تغییر نمی‌کنند.
                </div>
            @elseif($drawCampaign->status === DrawCampaignStatusEnum::COMPLETED)
                <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs leading-7 text-emerald-800">
                    قرعه‌کشی تکمیل شده و اطلاعات لازم برای صحت‌سنجی نتیجه در دسترس است.
                </div>
            @endif
        </div>

        @forelse($runs as $run)
            @php
                $runColor = match($run->status) {
                    DrawRunStatusEnum::PENDING => 'yellow',
                    DrawRunStatusEnum::RUNNING => 'brand',
                    DrawRunStatusEnum::COMPLETED => 'green',
                    DrawRunStatusEnum::FAILED => 'red',
                };
                $primaryWinners = $run->winners->where('kind', DrawWinnerKindEnum::PRIMARY);
                $reserveWinners = $run->winners->where('kind', DrawWinnerKindEnum::RESERVE);
                $seedCommitmentValid = $run->status === DrawRunStatusEnum::COMPLETED && $run->seed_reveal
                    ? hash_equals((string)$run->seed_commitment, hash('sha256', base64_decode($run->seed_reveal, true) ?: ''))
                    : null;
            @endphp

            <div id="draw-run-{{ $run->public_token }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <div class="flex flex-col justify-between gap-4 xl:flex-row xl:items-start">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="text-sm font-black text-slate-900">{{ $run->type->label() }} #{{ $run->id }}</div>
                                <x-admin.badge :color="$runColor" size="md">{{ $run->status->label() }}</x-admin.badge>
                                <x-admin.badge color="slate" size="sm" dir="ltr">{{ $run->algorithm }}</x-admin.badge>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-400">
                                <span>شروع کننده: {{ $run->starter?->full_name ?? 'سیستم' }}</span>
                                <span>ایجاد: {{ jalali_date($run->created_at, 'Y/m/d H:i:s') }}</span>
                                @if($run->completed_at)<span>تکمیل: {{ jalali_date($run->completed_at, 'Y/m/d H:i:s') }}</span>@endif
                            </div>
                        </div>

                        @if($run->status === DrawRunStatusEnum::FAILED)
                            @can('raffle-draw.retry')
                                <x-admin.form
                                    action="{{ route('admin.draw-campaigns.draws.retry', [$drawCampaign, $run]) }}"
                                    method="post"
                                    class="js-confirm"
                                    data-confirm-title="اجرای دوباره قرعه‌کشی"
                                    data-confirm-text="اجرا با همان اطلاعات اولیه تکرار می‌شود و نتیجه تصادفی تازه‌ای ساخته نخواهد شد."
                                >
                                    <x-admin.form.button type="submit" variant="solid">تلاش دوباره</x-admin.form.button>
                                </x-admin.form>
                            @endcan
                        @endif
                    </div>

                    @if($run->failure_reason)
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs leading-7 text-red-700">
                            {{ $run->failure_reason }}
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-0 divide-y divide-slate-100 lg:grid-cols-2 lg:divide-x lg:divide-y-0 lg:divide-x-reverse">
                    <div class="p-5">
                        <div class="mb-3 text-xs font-extrabold text-slate-700">اطلاعات صحت‌سنجی</div>
                        <div class="space-y-3 text-xs">
                            <div>
                                <div class="mb-1 text-slate-400">تعهد اولیه</div>
                                <code class="block overflow-x-auto rounded-xl bg-slate-950 px-3 py-2 text-[11px] text-slate-100" dir="ltr">{{ $run->seed_commitment ?: '—' }}</code>
                            </div>

                            <div>
                                <div class="mb-1 text-slate-400">شناسه محاسباتی بلیط‌های واجد شرایط</div>
                                <code class="block overflow-x-auto rounded-xl bg-slate-950 px-3 py-2 text-[11px] text-slate-100" dir="ltr">{{ $run->eligible_set_hash ?: 'پس از تکمیل محاسبه می شود' }}</code>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <div class="text-[11px] text-slate-400">بلیط واجد شرایط</div>
                                    <div class="mt-1 text-lg font-black text-slate-900">{{ number_format($run->eligible_ticket_count) }}</div>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <div class="text-[11px] text-slate-400">نتیجه ثبت‌شده</div>
                                    <div class="mt-1 text-lg font-black text-slate-900">{{ number_format($run->winners->count()) }}</div>
                                </div>
                            </div>

                            @if($run->status === DrawRunStatusEnum::COMPLETED && $run->seed_reveal)
                                <div>
                                    <div class="mb-1 flex items-center justify-between gap-2">
                                        <span class="text-slate-400">مقدار اولیه آشکارشده</span>
                                        <x-admin.badge :color="$seedCommitmentValid ? 'green' : 'red'" size="sm">
                                            {{ $seedCommitmentValid ? 'معتبر' : 'عدم تطابق' }}
                                        </x-admin.badge>
                                    </div>
                                    <code class="block overflow-x-auto rounded-xl bg-slate-950 px-3 py-2 text-[11px] text-slate-100" dir="ltr">{{ $run->seed_reveal }}</code>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="mb-3 text-xs font-extrabold text-slate-700">نتیجه</div>

                        @if($run->status === DrawRunStatusEnum::COMPLETED)
                            <div class="space-y-4">
                                @foreach($drawCampaign->prizes as $prize)
                                    @php
                                        $prizePrimary = $primaryWinners->where('prize_id', $prize->id)->sortBy('position');
                                        $prizeReserve = $reserveWinners->where('prize_id', $prize->id)->sortBy('position');
                                    @endphp
                                    <div class="rounded-2xl border border-slate-200 p-3">
                                        <div class="mb-3 flex items-center justify-between gap-3">
                                            <div class="text-xs font-extrabold text-slate-800">{{ $prize->title }}</div>
                                            <span class="text-[11px] text-slate-400">{{ $prizePrimary->count() }} برنده اصلی</span>
                                        </div>

                                        <div class="space-y-2">
                                            @foreach($prizePrimary as $winner)
                                                <div class="flex items-center justify-between gap-3 rounded-xl bg-emerald-50 px-3 py-2">
                                                    <div>
                                                        <div class="text-xs font-black text-emerald-800">#{{ $winner->position }} {{ $winner->user?->full_name }}</div>
                                                        <div class="mt-1 text-[10px] text-emerald-600" dir="ltr">{{ $winner->ticket?->code }}</div>
                                                    </div>
                                                    <x-admin.badge color="green" size="sm">برنده اصلی</x-admin.badge>
                                                </div>
                                            @endforeach

                                            @foreach($prizeReserve as $winner)
                                                <div class="flex items-center justify-between gap-3 rounded-xl bg-amber-50 px-3 py-2">
                                                    <div>
                                                        <div class="text-xs font-bold text-amber-800">ذخیره #{{ $winner->position }} · {{ $winner->user?->full_name }}</div>
                                                        <div class="mt-1 text-[10px] text-amber-600" dir="ltr">{{ $winner->ticket?->code }}</div>
                                                    </div>
                                                    <x-admin.badge color="yellow" size="sm">ذخیره</x-admin.badge>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($run->status === DrawRunStatusEnum::FAILED)
                            <div class="rounded-2xl bg-red-50 px-4 py-8 text-center text-xs leading-7 text-red-700">
                                نتیجه‌ای ثبت نشده است. پس از رفع مشکل، همین اجرا را دوباره انجام دهید.
                            </div>
                        @else
                            <div class="rounded-2xl bg-brand-50 px-4 py-8 text-center text-xs leading-7 text-brand-700">
                                نتیجه در حال پردازش است. این صفحه چند لحظه دیگر خودکار تازه‌سازی می‌شود.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                <div class="text-sm font-black text-slate-800">هنوز اجرایی ثبت نشده است</div>
                <p class="mt-2 text-xs leading-7 text-slate-500">پس از بسته شدن دریافت بلیط و رسیدن زمان قرعه‌کشی، اجرا را از همین صفحه شروع کنید.</p>
            </div>
        @endforelse

        <x-admin.list.pagination :model="$runs"/>
    </div>
@endsection

@if($activeRun && in_array($activeRun->status, [\App\Domain\Raffle\Enums\DrawRunStatusEnum::PENDING, \App\Domain\Raffle\Enums\DrawRunStatusEnum::RUNNING], true))
    @push('scripts')
        <script>
            window.setTimeout(() => window.location.reload(), 5000);
        </script>
    @endpush
@endif
