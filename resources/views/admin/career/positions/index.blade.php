@extends('admin.layout')

@section('title', 'موقعیت‌های شغلی')

@section('content')
    <x-admin.form method="get" action="{{ route('admin.career-positions.index') }}" class="space-y-5">
        <x-admin.filters.panel :open="count($filters) > 0">
            <x-admin.filters.header title="مدیریت و جستجوی موقعیت‌های شغلی">
                <x-slot:actions>
                    @can('career-position.create')
                        <x-admin.form.button tag="a" href="{{ route('admin.career-positions.create') }}" variant="solid">+ افزودن موقعیت جدید</x-admin.form.button>
                    @endcan
                </x-slot:actions>
            </x-admin.filters.header>
            <x-admin.filters.body>
                <x-admin.filters.grid>
                    <x-admin.filters.item label="شناسه"><x-admin.form.input name="id" :value="request('id')"/></x-admin.filters.item>
                    <x-admin.filters.item label="عنوان"><x-admin.form.input name="title" :value="request('title')"/></x-admin.filters.item>
                    <x-admin.filters.item label="تیم / حوزه"><x-admin.form.input name="department" :value="request('department')"/></x-admin.filters.item>
                    <x-admin.filters.item label="وضعیت">
                        <x-admin.form.select name="status" :options="['' => 'همه وضعیت‌ها', 'draft' => 'پیش‌نویس', 'active' => 'فعال', 'closed' => 'بسته']" :value="request('status')"/>
                    </x-admin.filters.item>
                </x-admin.filters.grid>
                <x-admin.filters.actions>
                    <x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>
                    @if(count($filters))
                        <x-admin.form.button tag="a" href="{{ route('admin.career-positions.index') }}" variant="secondary">حذف فیلترها</x-admin.form.button>
                    @endif
                </x-admin.filters.actions>
            </x-admin.filters.body>
        </x-admin.filters.panel>
    </x-admin.form>

    <div class="mt-5 space-y-4">
        <x-admin.list.table>
            <x-admin.list.thead><tr>
                <x-admin.list.th>عنوان</x-admin.list.th>
                <x-admin.list.th>تیم / حوزه</x-admin.list.th>
                <x-admin.list.th>نوع همکاری</x-admin.list.th>
                <x-admin.list.th>مکان</x-admin.list.th>
                <x-admin.list.th>وضعیت</x-admin.list.th>
                <x-admin.list.th>تاریخ انتشار</x-admin.list.th>
                <x-admin.list.th>درخواست‌ها</x-admin.list.th>
                <x-admin.list.th>تاریخ ثبت</x-admin.list.th>
            </tr></x-admin.list.thead>
            <x-admin.list.tbody>
                @forelse($positions as $position)
                    <x-admin.list.tr>
                        <x-admin.list.td>
                            <strong class="text-slate-800">{{ $position->title }}</strong>
                            <div class="text-xs text-slate-500" dir="ltr">#{{ $position->id }} · {{ $position->slug }}</div>
                            <x-admin.list.row-actions>
                                @can('career-position.edit')<x-admin.list.action tag="a" href="{{ route('admin.career-positions.edit', $position) }}" variant="primary">ویرایش</x-admin.list.action>@endcan
                                @can('career-position.delete')
                                    <x-admin.form action="{{ route('admin.career-positions.destroy', $position) }}" method="delete" class="js-confirm" data-confirm-title="حذف موقعیت" data-confirm-text="این موقعیت از فهرست حذف می‌شود. درخواست‌های ثبت شده محفوظ می‌مانند.">
                                        <x-admin.list.action tag="button" type="submit" variant="danger">حذف</x-admin.list.action>
                                    </x-admin.form>
                                @endcan
                            </x-admin.list.row-actions>
                        </x-admin.list.td>
                        <x-admin.list.td>{{ $position->department ?: '—' }}</x-admin.list.td>
                        <x-admin.list.td>{{ $position->employment_type ?: '—' }}</x-admin.list.td>
                        <x-admin.list.td>{{ $position->location ?: '—' }}</x-admin.list.td>
                        <x-admin.list.td>{{ ['draft' => 'پیش‌نویس', 'active' => 'فعال', 'closed' => 'بسته'][$position->status] ?? $position->status }}</x-admin.list.td>
                        <x-admin.list.td>{{ $position->published_at ? jalali_date($position->published_at, 'Y/m/d') : '—' }}</x-admin.list.td>
                        <x-admin.list.td>
                            @can('career-application.list')
                                <a class="text-brand-700" href="{{ route('admin.career-applications.index', ['career_position_id' => $position->id]) }}">{{ $position->applications_count }}</a>
                            @else
                                {{ $position->applications_count }}
                            @endcan
                        </x-admin.list.td>
                        <x-admin.list.td>{{ $position->created_at?->format('Y/m/d') }}</x-admin.list.td>
                    </x-admin.list.tr>
                @empty
                    <x-admin.list.tr><x-admin.list.td colspan="8" class="text-center">موقعیتی ثبت نشده است.</x-admin.list.td></x-admin.list.tr>
                @endforelse
            </x-admin.list.tbody>
        </x-admin.list.table>
        <x-admin.list.pagination :model="$positions"/>
    </div>
@endsection
