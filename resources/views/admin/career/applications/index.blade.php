@extends('admin.layout')

@section('title', 'درخواست‌های همکاری')

@section('content')
    <x-admin.form method="get" action="{{ route('admin.career-applications.index') }}" class="space-y-5">
        <x-admin.filters.panel :open="count($filters) > 0">
            <x-admin.filters.header title="جستجوی درخواست‌های همکاری"/>
            <x-admin.filters.body>
                <x-admin.filters.grid>
                    <x-admin.filters.item label="نام، موبایل یا ایمیل"><x-admin.form.input name="q" :value="request('q')"/></x-admin.filters.item>
                    <x-admin.filters.item label="موقعیت"><x-admin.form.select name="career_position_id" :options="['' => 'همه موقعیت‌ها'] + $positions" :value="request('career_position_id')"/></x-admin.filters.item>
                    <x-admin.filters.item label="وضعیت"><x-admin.form.select name="status" :options="['' => 'همه وضعیت‌ها', 'new' => 'جدید', 'reviewing' => 'در حال بررسی', 'interview' => 'مصاحبه', 'accepted' => 'پذیرفته', 'rejected' => 'رد شده']" :value="request('status')"/></x-admin.filters.item>
                    <x-admin.filters.item label="مشاهده"><x-admin.form.select name="viewed" :options="['' => 'همه', 'unseen' => 'مشاهده‌نشده', 'seen' => 'مشاهده‌شده']" :value="request('viewed')"/></x-admin.filters.item>
                </x-admin.filters.grid>
                <x-admin.filters.actions>
                    <x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>
                    @if(count($filters))<x-admin.form.button tag="a" href="{{ route('admin.career-applications.index') }}" variant="secondary">حذف فیلترها</x-admin.form.button>@endif
                </x-admin.filters.actions>
            </x-admin.filters.body>
        </x-admin.filters.panel>
    </x-admin.form>

    <div class="mt-5 space-y-4">
        <x-admin.list.table>
            <x-admin.list.thead><tr>
                <x-admin.list.th>متقاضی</x-admin.list.th>
                <x-admin.list.th>موقعیت</x-admin.list.th>
                <x-admin.list.th>تلفن</x-admin.list.th>
                <x-admin.list.th>نوع همکاری</x-admin.list.th>
                <x-admin.list.th>وضعیت</x-admin.list.th>
                <x-admin.list.th>تاریخ ثبت</x-admin.list.th>
            </tr></x-admin.list.thead>
            <x-admin.list.tbody>
                @forelse($applications as $application)
                    <x-admin.list.tr>
                        <x-admin.list.td><a class="font-bold text-brand-700" href="{{ route('admin.career-applications.show', $application) }}">{{ $application->full_name }}</a>@if(!$application->viewed_at)<span class="mr-2 rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-bold text-rose-700">مشاهده‌نشده</span>@endif<div class="text-xs text-slate-500">#{{ $application->id }}</div></x-admin.list.td>
                        <x-admin.list.td>{{ $application->position_title ?: $application->position?->title ?: 'درخواست عمومی' }}</x-admin.list.td>
                        <x-admin.list.td dir="ltr">{{ $application->phone }}</x-admin.list.td>
                        <x-admin.list.td>{{ $application->preferred_cooperation_type ?: '—' }}</x-admin.list.td>
                        <x-admin.list.td>{{ ['new' => 'جدید', 'reviewing' => 'در حال بررسی', 'interview' => 'مصاحبه', 'accepted' => 'پذیرفته', 'rejected' => 'رد شده'][$application->status] ?? $application->status }}</x-admin.list.td>
                        <x-admin.list.td>{{ $application->created_at?->format('Y/m/d H:i') }}</x-admin.list.td>
                    </x-admin.list.tr>
                @empty
                    <x-admin.list.tr><x-admin.list.td colspan="6" class="text-center">درخواستی ثبت نشده است.</x-admin.list.td></x-admin.list.tr>
                @endforelse
            </x-admin.list.tbody>
        </x-admin.list.table>
        <x-admin.list.pagination :model="$applications"/>
    </div>
@endsection
