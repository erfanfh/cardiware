@extends('admin.layout')

@section('title', 'لیست تیکت‌ها')

@section('content')
    <x-admin.form.validation-errors/>

    @include('admin.support.tickets.partials.filters-panel')

    @php
        $ticketIds = $tickets->pluck('id')->values()->all();
    @endphp

    <div x-data="adminBulkSelection()" class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-2 transition-all duration-200">
            @php
                $baseQueryParams = request()->except(['status', 'page']);

                $statusTabs = [
                    [
                        'label' => 'همه',
                        'count' => $statusCounts['all'],
                        'href' => route('admin.tickets.index', $baseQueryParams),
                        'active' => !request('status'),
                    ],
                    [
                        'label' => 'باز',
                        'count' => $statusCounts['open'],
                        'href' => route('admin.tickets.index', array_merge($baseQueryParams, ['status' => 'open'])),
                        'active' => request('status') === 'open',
                    ],
                    [
                        'label' => 'پاسخ داده شده',
                        'count' => $statusCounts['answered'],
                        'href' => route('admin.tickets.index', array_merge($baseQueryParams, ['status' => 'answered'])),
                        'active' => request('status') === 'answered',
                    ],
                    [
                        'label' => 'بسته شده',
                        'count' => $statusCounts['closed'],
                        'href' => route('admin.tickets.index', array_merge($baseQueryParams, ['status' => 'closed'])),
                        'active' => request('status') === 'closed',
                    ],
                ];
            @endphp

            <div class="flex flex-wrap items-center gap-2">
                @foreach($statusTabs as $tab)
                    <a
                        href="{{ $tab['href'] }}"
                        class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium transition-all duration-200
                        {{ $tab['active']
                            ? 'border-slate-900 bg-slate-900 text-white shadow-sm'
                            : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300 hover:bg-slate-100'
                        }}"
                    >
                        <span>{{ $tab['label'] }}</span>

                        <span
                            class="inline-flex min-w-6 items-center justify-center rounded-lg px-1.5 py-0.5 text-xs font-bold
                            {{ $tab['active']
                                ? 'bg-white/15 text-white'
                                : 'border border-slate-200 bg-white text-slate-500'
                            }}"
                        >
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <x-admin.list.bulk-bar
            :action="route('admin.tickets.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند تیکت را انتخاب کنید."
            selected-text="تیکت انتخاب شده"
        />

        @include('admin.support.tickets.partials.list-table')

        <x-admin.list.pagination :model="$tickets"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js'])
@endpush
