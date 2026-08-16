@extends('admin.layout')

@section('title', 'کاربران باشگاه مخاطبان')

@section('content')
    @include('admin.loyalty.members.partials.filters-panel')

    <div class="mb-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @php
            $summaryCards = [
                ['label' => 'حساب امتیاز ساخته شده', 'value' => number_format($stats['accounts_count']), 'suffix' => 'کاربر'],
                ['label' => 'موجودی فعلی کل', 'value' => number_format($stats['total_balance']), 'suffix' => 'امتیاز'],
                ['label' => 'کل امتیاز کسب شده', 'value' => number_format($stats['total_lifetime_earned']), 'suffix' => 'امتیاز'],
                ['label' => 'کل امتیاز مصرف شده', 'value' => number_format($stats['total_lifetime_spent']), 'suffix' => 'امتیاز'],
            ];
        @endphp

        @foreach($summaryCards as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-500">{{ $card['label'] }}</div>
                <div class="mt-2 flex items-end gap-1.5">
                    <div class="text-xl font-black text-slate-900">{{ $card['value'] }}</div>
                    <div class="pb-0.5 text-[11px] font-bold text-slate-400">{{ $card['suffix'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    @include('admin.loyalty.members.partials.list-table')

    <div class="mt-4">
        <x-admin.list.pagination :model="$members"/>
    </div>
@endsection
