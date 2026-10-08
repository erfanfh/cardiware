@extends('admin.layout')

@section('title', 'تاریخچه تغییرات')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h1 class="text-xl font-extrabold text-slate-900">تاریخچه تغییرات</h1>
        <p class="mt-2 text-sm leading-7 text-slate-500">ایجاد، ویرایش، حذف و تغییر رابطه‌ها را با زمان و عامل انجام‌دهنده بررسی کنید.</p>
    </div>

    <x-admin.form method="get" action="{{ route('admin.audit-events.index') }}" class="space-y-5">
        <x-admin.filters.panel :open="count(array_filter($filters, fn ($value) => filled($value))) > 0">
            <x-admin.filters.header title="فیلتر تاریخچه"/>
            <x-admin.filters.body>
                <x-admin.filters.grid>
                    <x-admin.filters.item label="نوع تغییر">
                        <x-admin.form.select name="type" :options="['' => 'همه تغییرات'] + $eventTypes" :value="$filters['type'] ?? ''"/>
                    </x-admin.filters.item>
                    <x-admin.filters.item label="نوع مفهوم">
                        <x-admin.form.select name="subject_type" :options="['' => 'همه مفاهیم'] + $subjectTypes" :value="$filters['subject_type'] ?? ''"/>
                    </x-admin.filters.item>
                    <x-admin.filters.item label="شناسه مفهوم">
                        <x-admin.form.input name="subject_id" :value="$filters['subject_id'] ?? ''" placeholder="شناسه عددی یا UUID" dir="ltr"/>
                    </x-admin.filters.item>
                    <x-admin.filters.item label="عنوان مفهوم">
                        <x-admin.form.input name="q" :value="$filters['q'] ?? ''" placeholder="بخشی از نام یا عنوان"/>
                    </x-admin.filters.item>
                    <x-admin.filters.item label="شناسه عامل">
                        <x-admin.form.input name="actor_id" type="number" min="1" :value="$filters['actor_id'] ?? ''" placeholder="شناسه کاربر" dir="ltr"/>
                    </x-admin.filters.item>
                    <x-admin.filters.item label="از تاریخ">
                        <x-admin.form.date-picker name="from" :value="$filters['from'] ?? ''" placeholder="تاریخ شمسی" data-jdp-only-date dir="ltr"/>
                    </x-admin.filters.item>
                    <x-admin.filters.item label="تا تاریخ">
                        <x-admin.form.date-picker name="to" :value="$filters['to'] ?? ''" placeholder="تاریخ شمسی" data-jdp-only-date dir="ltr"/>
                    </x-admin.filters.item>
                </x-admin.filters.grid>
                <x-admin.filters.actions>
                    <x-admin.form.button type="submit" variant="solid">اعمال فیلتر</x-admin.form.button>
                    @if(count(array_filter($filters, fn ($value) => filled($value))))
                        <x-admin.form.button tag="a" href="{{ route('admin.audit-events.index') }}" variant="secondary">حذف فیلترها</x-admin.form.button>
                    @endif
                </x-admin.filters.actions>
            </x-admin.filters.body>
        </x-admin.filters.panel>
    </x-admin.form>

    <div class="space-y-4">
        <x-admin.list.table>
            <x-admin.list.thead><tr>
                <x-admin.list.th>زمان</x-admin.list.th>
                <x-admin.list.th>عملیات</x-admin.list.th>
                <x-admin.list.th>مفهوم</x-admin.list.th>
                <x-admin.list.th>عامل</x-admin.list.th>
                <x-admin.list.th>خلاصه تغییر</x-admin.list.th>
                <x-admin.list.th>جزئیات</x-admin.list.th>
            </tr></x-admin.list.thead>
            <x-admin.list.tbody>
                @forelse($events as $event)
                    <x-admin.list.tr>
                        <x-admin.list.td>
                            <div class="whitespace-nowrap text-sm font-semibold">{{ jalali_date($event->created_at, 'Y/m/d H:i:s') }}</div>
                            <div class="text-xs text-slate-400" dir="ltr">#{{ $event->id }}</div>
                        </x-admin.list.td>
                        <x-admin.list.td><span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $presenter->typeLabel($event->type) }}</span></x-admin.list.td>
                        <x-admin.list.td>
                            <div class="font-bold text-slate-800">{{ $presenter->subjectTypeLabel($event->subject_type) }}</div>
                            <div class="max-w-56 truncate text-xs text-slate-500" title="{{ $presenter->subjectLabel($event) }}">{{ $presenter->subjectLabel($event) }}</div>
                            <div class="text-[11px] text-slate-400" dir="ltr">{{ $event->subject_id }}</div>
                        </x-admin.list.td>
                        <x-admin.list.td>
                            <div class="font-medium">{{ $presenter->actorLabel($event) }}</div>
                            @if($event->user_id)<div class="text-xs text-slate-400" dir="ltr">#{{ $event->user_id }}</div>@endif
                        </x-admin.list.td>
                        <x-admin.list.td><span class="text-xs leading-6 text-slate-600">{{ $presenter->summary($event) }}</span></x-admin.list.td>
                        <x-admin.list.td><a class="font-bold text-brand-700 hover:underline" href="{{ route('admin.audit-events.show', $event) }}">مشاهده</a></x-admin.list.td>
                    </x-admin.list.tr>
                @empty
                    <x-admin.list.tr><x-admin.list.td colspan="6" class="py-10 text-center text-slate-500">رویدادی با این فیلترها پیدا نشد.</x-admin.list.td></x-admin.list.tr>
                @endforelse
            </x-admin.list.tbody>
        </x-admin.list.table>
        <x-admin.list.pagination :model="$events"/>
    </div>
@endsection
