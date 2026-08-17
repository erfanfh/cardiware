@extends('front.account.layout')

@php
    use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
    use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
@endphp

@section('page-content')
    <div class="space-y-5">
        <x-front.cart class="relative overflow-hidden !p-0">
            <div class="absolute inset-0 bg-gradient-to-l from-brand-50 via-white to-white dark:from-brand-950/35 dark:via-slate-900 dark:to-slate-900"></div>
            <div class="absolute -left-14 -top-14 h-40 w-40 rounded-full bg-brand-200/60 blur-3xl dark:bg-brand-500/10"></div>
            <div class="absolute -bottom-16 right-10 h-40 w-40 rounded-full bg-orange-100/80 blur-3xl dark:bg-orange-500/10"></div>

            <div class="relative p-4 sm:p-6">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="max-w-xl">
                        <div class="inline-flex items-center gap-2 rounded-xl border border-brand-100 bg-white/70 px-2.5 py-1.5 text-[10px] font-extrabold text-brand-700 dark:border-brand-500/20 dark:bg-slate-800/70 dark:text-brand-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                            قرعه کشی های من
                        </div>
                        <h1 class="mt-3 text-lg font-black text-slate-900 dark:text-white sm:text-xl">بلیط ها و شانس های تو، یکجا</h1>
                        <p class="mt-2 text-xs leading-7 text-slate-500 dark:text-slate-400 sm:text-sm">
                            وضعیت قرعه کشی هایی که در آن ها بلیط داری، کد تمام بلیط ها و بردهای ثبت شده را از این صفحه دنبال کن.
                        </p>
                        <a href="{{ route('raffles.index') }}" class="mt-4 inline-flex h-10 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600 dark:hover:bg-brand-400">
                            مشاهده همه قرعه کشی ها
                            <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 xl:w-[430px]">
                        @foreach([
                            ['label' => 'قرعه کشی', 'value' => $stats['campaigns_count']],
                            ['label' => 'بلیط فعال', 'value' => $stats['active_tickets_count']],
                            ['label' => 'کل بلیط', 'value' => $stats['total_tickets_count']],
                            ['label' => 'برد من', 'value' => $stats['winning_positions_count']],
                        ] as $stat)
                            <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-3 text-center shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-800/75">
                                <div class="text-[10px] font-extrabold text-slate-400">{{ $stat['label'] }}</div>
                                <div class="mt-1.5 text-xl font-black text-slate-900 dark:text-white">{{ number_format($stat['value']) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-front.cart>

        @if($winningRecords->isNotEmpty())
            <section>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-[10px] font-extrabold text-amber-600 dark:text-amber-300">خبر خوب</div>
                        <h2 class="mt-1 text-base font-black text-slate-900 dark:text-white">بردهای من</h2>
                    </div>
                    <span class="rounded-xl bg-amber-50 px-2.5 py-1.5 text-[10px] font-extrabold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">{{ number_format($winningRecords->count()) }} مورد</span>
                </div>

                <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
                    @foreach($winningRecords as $winner)
                        <article class="relative overflow-hidden rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-white p-4 shadow-sm dark:border-amber-500/20 dark:from-amber-500/10 dark:via-slate-900 dark:to-slate-900">
                            <div class="absolute -left-8 -top-8 h-24 w-24 rounded-full bg-amber-200/50 blur-2xl dark:bg-amber-500/10"></div>
                            <div class="relative flex items-start gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-sm [&_svg]:h-6 [&_svg]:w-6">
                                    @include('icons.medal-06')
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-sm font-black text-slate-900 dark:text-white">{{ $winner->prize?->title }}</h3>
                                        <span class="rounded-lg px-2 py-1 text-[9px] font-extrabold
                                            {{ $winner->status === DrawWinnerStatusEnum::DELIVERED
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                                : ($winner->status === DrawWinnerStatusEnum::CONFIRMED
                                                    ? 'bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300'
                                                    : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300') }}">
                                            {{ $winner->status->label() }}
                                        </span>
                                    </div>
                                    <a href="{{ route('raffles.show', $winner->campaign->slug) }}" class="mt-1 block truncate text-[11px] font-bold text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">
                                        {{ $winner->campaign->title }}
                                    </a>
                                    @if($winner->ticket)
                                        <div class="mt-2 text-[10px] font-bold tracking-wide text-slate-400" dir="ltr">{{ $winner->ticket->code }}</div>
                                    @endif
                                    @if($winner->confirmation_deadline_at && $winner->status === DrawWinnerStatusEnum::PENDING)
                                        <div class="mt-2 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                            مهلت تایید: {{ jalali_date($winner->confirmation_deadline_at, 'Y/m/d H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="my-raffles" class="scroll-mt-24">
            <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-[10px] font-extrabold text-brand-500 dark:text-brand-300">کمپین های شرکت کرده</div>
                    <h2 class="mt-1 text-base font-black text-slate-900 dark:text-white">قرعه کشی های من</h2>
                </div>
                <div class="text-[11px] text-slate-400">آخرین قرعه کشی هایی که برای آن ها بلیط داری.</div>
            </div>

            @if($campaigns->isNotEmpty())
                <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
                    @foreach($campaigns as $campaign)
                        <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-200 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-500/30">
                            <div class="flex flex-col sm:flex-row">
                                <a href="{{ route('raffles.show', $campaign->slug) }}" class="relative aspect-[16/8] shrink-0 overflow-hidden bg-slate-900 sm:aspect-auto sm:w-40">
                                    @if($campaign->coverMediaFile)
                                        <x-front.responsive-image
                                            :media="$campaign->coverMediaFile"
                                            :fallback="$campaign->coverMediaFile?->url"
                                            :alt="$campaign->title"
                                            variant="thumbnail"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            wrapper-class="h-full w-full"
                                            :skeleton="true"
                                            loading="lazy"
                                            sizes="160px"
                                        />
                                    @else
                                        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-brand-950 to-orange-950"></div>
                                        <div class="absolute inset-0 flex items-center justify-center text-white/60 [&_svg]:h-10 [&_svg]:w-10">@include('icons.award-03')</div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/50 to-transparent"></div>
                                </a>

                                <div class="min-w-0 flex-1 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <x-front.raffle.status-badge :status="$campaign->status"/>
                                            <a href="{{ route('raffles.show', $campaign->slug) }}" class="mt-2 block line-clamp-2 text-sm font-black leading-6 text-slate-900 transition hover:text-brand-600 dark:text-white dark:hover:text-brand-300">
                                                {{ $campaign->title }}
                                            </a>
                                        </div>
                                        <span class="text-slate-300 dark:text-slate-600 [&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <div class="rounded-xl bg-emerald-50 px-3 py-2 dark:bg-emerald-500/10">
                                            <div class="text-[9px] font-bold text-emerald-600 dark:text-emerald-300">بلیط فعال من</div>
                                            <div class="mt-0.5 text-sm font-black text-emerald-700 dark:text-emerald-200">{{ number_format($campaign->my_active_tickets_count) }}</div>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70">
                                            <div class="text-[9px] font-bold text-slate-400">کل بلیط من</div>
                                            <div class="mt-0.5 text-sm font-black text-slate-800 dark:text-slate-200">{{ number_format($campaign->my_total_tickets_count) }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a href="{{ route('account.raffles', ['campaign' => $campaign->public_token]) }}#my-tickets" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-50 px-2.5 py-1.5 text-[10px] font-extrabold text-brand-700 transition hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/15">
                                            بلیط های این قرعه کشی
                                        </a>
                                        <a href="{{ route('raffles.show', $campaign->slug) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-2.5 py-1.5 text-[10px] font-extrabold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                            جزئیات
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($campaigns->hasPages())
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                        <x-front.list.pagination :model="$campaigns"/>
                    </div>
                @endif
            @else
                <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-7 text-center dark:border-slate-700 dark:bg-slate-900">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 [&_svg]:h-6 [&_svg]:w-6">@include('icons.award-03')</div>
                    <div class="mt-3 text-sm font-black text-slate-800 dark:text-slate-200">هنوز در قرعه کشی ای بلیط نداری</div>
                    <p class="mx-auto mt-2 max-w-md text-xs leading-6 text-slate-400">بعد از صدور اولین بلیط، قرعه کشی و کد شانس های تو اینجا اضافه می شوند.</p>
                    <a href="{{ route('raffles.index') }}" class="mt-4 inline-flex h-10 items-center justify-center rounded-2xl bg-brand-500 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600">مشاهده قرعه کشی ها</a>
                </div>
            @endif
        </section>

        <section id="my-tickets" class="scroll-mt-24">
            <x-front.cart class="!p-4 sm:!p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div class="text-[10px] font-extrabold text-sky-500 dark:text-sky-300">کدهای شانس</div>
                        <h2 class="mt-1 text-base font-black text-slate-900 dark:text-white">همه بلیط های من</h2>
                        <p class="mt-1 text-[11px] leading-6 text-slate-400">
                            @if($selectedCampaign)
                                فیلتر شده برای «{{ $selectedCampaign->title }}»
                            @else
                                تمام بلیط های صادر شده برای حساب کاربری تو
                            @endif
                        </p>
                    </div>

                    <form method="get" action="{{ route('account.raffles') }}#my-tickets" class="flex w-full gap-2 lg:w-auto">
                        <select name="campaign" class="h-10 min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 outline-none transition focus:border-brand-400 focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:focus:border-brand-500 dark:focus:ring-brand-500/10 lg:w-64">
                            <option value="">همه قرعه کشی ها</option>
                            @foreach($filterCampaigns as $filterCampaign)
                                <option value="{{ $filterCampaign->public_token }}" @selected(request('campaign') === $filterCampaign->public_token)>{{ $filterCampaign->title }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="h-10 shrink-0 rounded-xl bg-slate-900 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600 dark:bg-slate-700 dark:hover:bg-brand-500">فیلتر</button>
                    </form>
                </div>

                @if($tickets->isNotEmpty())
                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 xl:hidden">
                        @foreach($tickets as $ticket)
                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <a href="{{ route('raffles.show', $ticket->campaign->slug) }}" class="line-clamp-1 text-xs font-extrabold text-slate-700 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-300">{{ $ticket->campaign->title }}</a>
                                        <div class="mt-2 truncate text-xs font-black tracking-wide text-slate-900 dark:text-white" dir="ltr" title="{{ $ticket->code }}">{{ $ticket->code }}</div>
                                    </div>
                                    <span class="rounded-lg px-2 py-1 text-[9px] font-extrabold {{ $ticket->status === DrawTicketStatusEnum::ACTIVE ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">{{ $ticket->status->label() }}</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3 text-[10px] text-slate-400">
                                    <span>{{ jalali_date($ticket->issued_at, 'Y/m/d H:i') }}</span>
                                    @if($ticket->winnerRecords->isNotEmpty())
                                        <span class="font-extrabold text-amber-600 dark:text-amber-300">منتخب قرعه کشی</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-4 hidden overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 xl:block">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-50 text-[10px] font-extrabold text-slate-400 dark:bg-slate-800/70">
                                <tr>
                                    <th class="px-4 py-3">قرعه کشی</th>
                                    <th class="px-4 py-3">کد بلیط</th>
                                    <th class="px-4 py-3">وضعیت</th>
                                    <th class="px-4 py-3">زمان صدور</th>
                                    <th class="px-4 py-3">نتیجه</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                @foreach($tickets as $ticket)
                                    <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('raffles.show', $ticket->campaign->slug) }}" class="font-extrabold text-slate-700 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-300">{{ $ticket->campaign->title }}</a>
                                        </td>
                                        <td class="px-4 py-3 font-black tracking-wide text-slate-900 dark:text-white" dir="ltr">{{ $ticket->code }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-lg px-2 py-1 text-[9px] font-extrabold {{ $ticket->status === DrawTicketStatusEnum::ACTIVE ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $ticket->status->label() }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-400">{{ jalali_date($ticket->issued_at, 'Y/m/d H:i') }}</td>
                                        <td class="px-4 py-3">
                                            @if($ticket->winnerRecords->isNotEmpty())
                                                <span class="font-extrabold text-amber-600 dark:text-amber-300">منتخب قرعه کشی</span>
                                            @else
                                                <span class="text-slate-300 dark:text-slate-600">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($tickets->hasPages())
                        <div class="mt-4">
                            <x-front.list.pagination :model="$tickets"/>
                        </div>
                    @endif
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-xs leading-7 text-slate-400 dark:border-slate-700 dark:bg-slate-800/50">
                        بلیطی با فیلتر انتخاب شده پیدا نشد.
                    </div>
                @endif
            </x-front.cart>
        </section>
    </div>
@endsection
