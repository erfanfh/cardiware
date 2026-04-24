@extends('admin.layout')

@section('title', 'لیست دیدگاه‌ها')

@section('content')
    @include('admin.discussion.comments.partials.filters-panel')

    @php
        $commentIds = $comments->pluck('id')->values()->all();

        $commentStatuses = $comments
            ->mapWithKeys(fn($comment) => [
                $comment->id => $comment->status?->value,
            ])
            ->all();
    @endphp

    <div
        x-data="{
            ...adminBulkSelection(),
            ...adminCommentActions({
                bulkActionUrl: @js(route('admin.comments.bulk-action')),
                csrfToken: @js(csrf_token()),
                initialStatuses: @js($commentStatuses),
            })
        }"
        class="space-y-4"
    >
        <div class="rounded-2xl border px-4 py-2 transition-all duration-200 border-slate-200 bg-white">
            @php
                $baseQueryParams = request()->except(['status', 'type', 'page']);

                $statusTabs = [
                    [
                        'label' => 'همه',
                        'count' => $statusCounts['all'],
                        'href' => route('admin.comments.index', $baseQueryParams),
                        'active' => !request('status') && !request('type'),
                    ],
                    [
                        'label' => 'در انتظار تایید',
                        'count' => $statusCounts['pending'],
                        'href' => route('admin.comments.index', array_merge($baseQueryParams, ['status' => 'pending'])),
                        'active' => request('status') === 'pending',
                    ],
                    [
                        'label' => 'تایید شده',
                        'count' => $statusCounts['approved'],
                        'href' => route('admin.comments.index', array_merge($baseQueryParams, ['status' => 'approved'])),
                        'active' => request('status') === 'approved',
                    ],
                    [
                        'label' => 'رد شده',
                        'count' => $statusCounts['rejected'],
                        'href' => route('admin.comments.index', array_merge($baseQueryParams, ['status' => 'rejected'])),
                        'active' => request('status') === 'rejected',
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
                        : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:border-slate-300'
                    }}"
                    >
                        <span>{{ $tab['label'] }}</span>

                        <span
                            class="inline-flex min-w-6 items-center justify-center rounded-lg px-1.5 py-0.5 text-xs font-bold
                    {{ $tab['active']
                        ? 'bg-white/15 text-white'
                        : 'bg-white text-slate-500 border border-slate-200'
                    }}"
                        >
                    {{ $tab['count'] }}
                </span>
                    </a>
                @endforeach
            </div>
        </div>

        <x-admin.list.bulk-bar
            :action="route('admin.comments.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند دیدگاه را انتخاب کنید."
            selected-text="دیدگاه انتخاب شده"
        />

        @include('admin.discussion.comments.partials.list-table')

        <x-admin.list.pagination :model="$comments"/>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js', 'resources/js/admin/comment-actions.js'])
@endpush
