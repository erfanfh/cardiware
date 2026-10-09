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
                    @php
                        $status = match ($application->status) {
                            'new' => ['جدید', 'blue'],
                            'reviewing' => ['در حال بررسی', 'yellow'],
                            'interview' => ['مصاحبه', 'brand'],
                            'accepted' => ['پذیرفته', 'green'],
                            'rejected' => ['رد شده', 'red'],
                            default => [$application->status, 'slate'],
                        };
                    @endphp
                    <x-admin.list.tr :tone="$application->viewed_at ? 'default' : 'danger'">
                        <x-admin.list.td>
                            <div class="min-w-[190px]">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1.5">
                                    <a class="font-bold leading-6 text-slate-800 transition hover:text-brand-700" href="{{ route('admin.career-applications.show', $application) }}">{{ $application->full_name }}</a>
                                    @unless($application->viewed_at)
                                        <x-admin.badge color="red" class="shrink-0 whitespace-nowrap">مشاهده‌نشده</x-admin.badge>
                                    @endunless
                                </div>
                                <div class="mt-1 text-xs text-slate-500">#{{ $application->id }}</div>
                                <x-admin.list.row-actions>
                                    <x-admin.list.action tag="a" href="{{ route('admin.career-applications.show', $application) }}" variant="primary">مشاهده</x-admin.list.action>
                                    @if($application->resume_path && $application->resume_download_token)
                                        <span class="text-slate-300" aria-hidden="true">|</span>
                                        <x-admin.list.action tag="a" href="{{ route('admin.career-applications.resume', [$application, $application->resume_download_token]) }}">دانلود رزومه</x-admin.list.action>
                                    @endif
                                </x-admin.list.row-actions>
                            </div>
                        </x-admin.list.td>
                        <x-admin.list.td><span class="font-medium text-slate-700">{{ $application->position_title ?: $application->position?->title ?: 'درخواست عمومی' }}</span></x-admin.list.td>
                        <x-admin.list.td><span dir="ltr" class="inline-block whitespace-nowrap">{{ $application->phone }}</span></x-admin.list.td>
                        <x-admin.list.td>{{ $application->preferred_cooperation_type ?: '—' }}</x-admin.list.td>
                        <x-admin.list.td><x-admin.badge :color="$status[1]" size="md" class="whitespace-nowrap">{{ $status[0] }}</x-admin.badge></x-admin.list.td>
                        <x-admin.list.td muted><div class="whitespace-nowrap text-xs leading-6"><div>{{ jalali_date($application->created_at, 'Y/m/d') }}</div><div>{{ jalali_date($application->created_at, 'H:i') }}</div></div></x-admin.list.td>
                    </x-admin.list.tr>
                @empty
                    <x-admin.list.tr><x-admin.list.td colspan="6" class="text-center">درخواستی ثبت نشده است.</x-admin.list.td></x-admin.list.tr>
                @endforelse
            </x-admin.list.tbody>
        </x-admin.list.table>
        <x-admin.list.pagination :model="$applications"/>
    </div>
@endsection
