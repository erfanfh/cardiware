@extends('admin.layout')

@section('title', 'برترین‌های باشگاه')

@section('content')
    <x-admin.page>
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="max-w-3xl text-sm leading-7 text-slate-500">
                هر شب ماه، فصل و سالِ تمام‌شده بسته می‌شود و نفرات ۱ تا ۳ (بر اساس XP همان دوره، بدون کارمندان) جایزه می‌گیرند.
                جوایز و حد نصاب کاربر برتر سال در «تنظیمات باشگاه مخاطبان» تعیین می‌شوند. دوره‌هایی که پیش از راه‌اندازی این قابلیت تمام شده‌اند جایزه ندارند.
            </p>
            <div class="flex gap-2">
                <a href="{{ route('loyalty.hall-of-fame') }}" target="_blank"
                   class="inline-flex h-10 items-center rounded-xl border border-slate-200 px-4 text-xs font-extrabold text-slate-600 hover:bg-slate-50">مشاهده تالار افتخار</a>
                <form method="post" action="{{ route('admin.loyalty-champions.close') }}">
                    @csrf
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-brand-500 px-4 text-xs font-extrabold text-white hover:bg-brand-600">بررسی دوره‌های تمام‌شده</button>
                </form>
            </div>
        </div>

        @if($pendingGrandPrizes->isNotEmpty())
            <x-admin.metabox title="جایزه‌های ویژه در انتظار تحویل">
                <div class="space-y-2">
                    @foreach($pendingGrandPrizes as $champion)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                            <div class="text-sm">
                                <b>🎁 {{ $champion->grand_prize }}</b> —
                                <a href="{{ route('admin.loyalty-members.show', $champion->user_id) }}" class="font-bold text-brand-600">{{ $champion->user?->display_name ?: $champion->user?->username ?: 'کاربر #' . $champion->user_id }}</a>
                                ({{ $champion->title() }})
                            </div>
                            <form method="post" action="{{ route('admin.loyalty-champions.deliver', $champion) }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-extrabold text-white">تحویل داده شد</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </x-admin.metabox>
        @endif

        <x-admin.metabox title="دوره‌های بسته‌شده">
            @if($closures->isEmpty())
                <p class="text-sm text-slate-500">هنوز دوره‌ای بسته نشده است.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b border-slate-200 text-right text-xs text-slate-400">
                            <th class="px-3 py-3 font-bold">دوره</th>
                            <th class="px-3 py-3 font-bold">رتبه</th>
                            <th class="px-3 py-3 font-bold">کاربر</th>
                            <th class="px-3 py-3 font-bold">XP</th>
                            <th class="px-3 py-3 font-bold">جوایز</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($closures as $closure)
                            @forelse($closure->champions as $champion)
                                <tr class="border-b border-slate-100">
                                    <td class="px-3 py-2.5 font-bold">{{ $loop->first ? $closure->period_label : '' }}</td>
                                    <td class="px-3 py-2.5">{{ $champion->trophy() }} {{ $champion->rank }}</td>
                                    <td class="px-3 py-2.5">
                                        <a href="{{ route('admin.loyalty-members.show', $champion->user_id) }}" class="font-bold text-brand-600">{{ $champion->user?->display_name ?: $champion->user?->username ?: '#' . $champion->user_id }}</a>
                                    </td>
                                    <td class="px-3 py-2.5">{{ number_format($champion->score) }}</td>
                                    <td class="px-3 py-2.5 text-xs leading-6 text-slate-600">
                                        {{ number_format($champion->points_awarded) }} امتیاز
                                        @if($champion->coupon_code) · کد {{ $champion->coupon_code }} @endif
                                        @if($champion->raffle_tickets) · {{ number_format($champion->raffle_tickets) }} کد قرعه‌کشی @endif
                                        @if($champion->grand_prize) · 🎁 {{ $champion->grand_prize }} ({{ $champion->grand_prize_status === 'delivered' ? 'تحویل شد' : 'در انتظار تحویل' }}) @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-b border-slate-100">
                                    <td class="px-3 py-2.5 font-bold">{{ $closure->period_label }}</td>
                                    <td colspan="4" class="px-3 py-2.5 text-xs text-slate-400">برنده‌ای نداشت</td>
                                </tr>
                            @endforelse
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $closures->links() }}</div>
            @endif
        </x-admin.metabox>
    </x-admin.page>
@endsection
