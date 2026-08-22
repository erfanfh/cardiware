@extends('admin.layout')

@section('title', 'برندگان - ' . $drawCampaign->title)

@section('content')
    @include('admin.raffle.campaigns.partials.nav')
    <div class="mt-4 flex justify-end"><x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.exports.winners', $drawCampaign) }}" variant="secondary">خروجی CSV برندگان و تحویل</x-admin.form.button></div>

    <div class="mt-5 space-y-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-lg font-black text-slate-900">مدیریت برندگان</h1>
                        <x-admin.badge color="emerald" size="md">{{ $drawCampaign->status->label() }}</x-admin.badge>
                    </div>
                    <p class="mt-2 max-w-3xl text-xs leading-7 text-slate-500">
                        نتیجه اولیه Draw هیچ وقت بازنویسی نمی شود. رد، انقضا، Promote ذخیره و Redraw به صورت رکوردهای مستقل و قابل Audit نگهداری می شوند.
                    </p>
                </div>
                <div class="text-xs text-slate-400" dir="ltr">{{ $drawCampaign->public_token }}</div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            @foreach([
                ['label'=>'برنده اصلی انتخاب شده','value'=>$stats['primary'],'class'=>'text-slate-900'],
                ['label'=>'در انتظار تایید','value'=>$stats['pending'],'class'=>'text-amber-600'],
                ['label'=>'تایید شده','value'=>$stats['confirmed'],'class'=>'text-emerald-700'],
                ['label'=>'تحویل شده','value'=>$stats['delivered'],'class'=>'text-brand-700'],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-bold text-slate-500">{{ $card['label'] }}</div>
                    <div class="mt-2 text-2xl font-black {{ $card['class'] }}">{{ number_format($card['value']) }}</div>
                </div>
            @endforeach
        </div>

        @forelse($drawCampaign->prizes as $prize)
            @php
                $prizeWinners = $byPrize->get($prize->id, collect());
                $primaryWinners = $prizeWinners->where('kind', \App\Domain\Raffle\Enums\DrawWinnerKindEnum::PRIMARY);
                $reserveWinners = $prizeWinners->where('kind', \App\Domain\Raffle\Enums\DrawWinnerKindEnum::RESERVE);
            @endphp

            <x-admin.metabox :title="$prize->title">
                <div class="mb-4 flex flex-col justify-between gap-3 rounded-2xl bg-slate-50 p-4 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3">
                        <x-admin.list.thumbnail :src="$prize->mediaFile?->url" :alt="$prize->title" size="md-square"/>
                        <div>
                            <div class="text-sm font-black text-slate-800">{{ $prize->title }}</div>
                            <div class="mt-1 text-xs text-slate-500">
                                {{ number_format($prize->quantity) }} جایگاه اصلی · {{ number_format($prize->effectiveReserveWinnersCount()) }} ذخیره برنامه ریزی شده
                            </div>
                        </div>
                    </div>
                    @if($prize->estimated_value)
                        <x-admin.badge color="brand" size="md">ارزش تقریبی {{ number_format($prize->estimated_value) }} تومان</x-admin.badge>
                    @endif
                </div>

                <div class="space-y-4">
                    @foreach($primaryWinners as $winner)
                        @include('admin.raffle.winners.partials.winner-card', ['winner' => $winner, 'isReserveSection' => false])
                    @endforeach

                    @if($reserveWinners->isNotEmpty())
                        <div class="border-t border-slate-200 pt-4">
                            <div class="mb-3 text-xs font-black text-slate-500">برندگان ذخیره</div>
                            <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
                                @foreach($reserveWinners as $winner)
                                    @include('admin.raffle.winners.partials.winner-card', ['winner' => $winner, 'isReserveSection' => true])
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($prizeWinners->isEmpty())
                        <div class="rounded-2xl border border-dashed border-slate-300 py-8 text-center text-xs text-slate-400">
                            هنوز Winner برای این جایزه ایجاد نشده است.
                        </div>
                    @endif
                </div>
            </x-admin.metabox>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                جایزه ای برای این قرعه کشی ثبت نشده است.
            </div>
        @endforelse
    </div>
@endsection
