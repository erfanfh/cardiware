@extends('admin.layout')
@section('title', 'نظرسنجی‌ها')
@section('content')
    <x-admin.metabox title="مدیریت و جستجوی نظرسنجی‌ها" :collapsible="false" class="mb-5">
        <x-admin.form method="get" action="{{ route('admin.polls.index') }}">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <x-admin.form.input name="search" :value="request('search')" placeholder="جستجوی عنوان یا سؤال…"/>
                <x-admin.form.select name="status" :value="request('status')" placeholder="همه وضعیت‌ها" :options="collect(\App\Domain\Poll\Enums\PollStatusEnum::cases())->mapWithKeys(fn ($v) => [$v->value => $v->label()])->all()"/>
                <x-admin.form.select name="audience" :value="request('audience')" placeholder="همه مخاطبان" :options="collect(\App\Domain\Poll\Enums\PollAudienceEnum::cases())->mapWithKeys(fn ($v) => [$v->value => $v->label()])->all()"/>
                <div class="flex gap-2"><button class="flex-1 rounded-2xl bg-slate-900 px-4 py-2 text-xs font-black text-white">اعمال فیلتر</button><a href="{{ route('admin.polls.create') }}" class="flex-1 rounded-2xl bg-brand-500 px-4 py-2 text-center text-xs font-black text-white">نظرسنجی جدید</a></div>
            </div>
        </x-admin.form>
    </x-admin.metabox>

    <x-admin.list.table>
        <x-admin.list.thead><tr>
            <x-admin.list.th>نظرسنجی</x-admin.list.th><x-admin.list.th width="120px">وضعیت</x-admin.list.th><x-admin.list.th width="130px">مخاطب</x-admin.list.th><x-admin.list.th width="100px">پاسخ</x-admin.list.th><x-admin.list.th width="100px">بازدید</x-admin.list.th><x-admin.list.th width="170px">زمان‌بندی</x-admin.list.th>
        </tr></x-admin.list.thead>
        <x-admin.list.tbody>
            @forelse($polls as $poll)
                @php($color = match($poll->status) { \App\Domain\Poll\Enums\PollStatusEnum::PUBLISHED => 'green', \App\Domain\Poll\Enums\PollStatusEnum::ARCHIVED => 'yellow', default => 'slate' })
                <x-admin.list.tr>
                    <x-admin.list.td>
                        <a href="{{ route('admin.polls.edit', $poll) }}" class="block max-w-xl truncate text-sm font-black text-slate-800 transition hover:text-brand-600">{{ $poll->title }}</a>
                        <div class="mt-1 text-[11px] text-slate-400" dir="ltr">/{{ $poll->slug }} · {{ $poll->active_options_count }} options</div>
                        <x-admin.list.row-actions>
                            <x-admin.list.action href="{{ route('admin.polls.edit', $poll) }}" variant="primary">ویرایش</x-admin.list.action>
                            <x-admin.list.action href="{{ route('admin.polls.analytics', $poll) }}">آمار</x-admin.list.action>
                            @if($poll->status === \App\Domain\Poll\Enums\PollStatusEnum::PUBLISHED)<x-admin.list.action href="{{ route('polls.show', $poll->slug) }}" target="_blank">نمایش</x-admin.list.action>@endif
                            <form action="{{ route('admin.polls.destroy', $poll) }}" method="post" class="inline" onsubmit="return confirm('این نظرسنجی حذف شود؟')">@csrf @method('DELETE')<button class="text-rose-600">حذف</button></form>
                        </x-admin.list.row-actions>
                    </x-admin.list.td>
                    <x-admin.list.td><x-admin.badge :color="$color">{{ $poll->status->label() }}</x-admin.badge></x-admin.list.td>
                    <x-admin.list.td><span class="text-xs font-bold text-slate-600">{{ $poll->audience->label() }}</span></x-admin.list.td>
                    <x-admin.list.td><span class="text-sm font-black text-brand-700">{{ number_format($poll->responses_count) }}</span></x-admin.list.td>
                    <x-admin.list.td><span class="text-sm font-black text-slate-700">{{ number_format($poll->views_count) }}</span></x-admin.list.td>
                    <x-admin.list.td muted><div class="text-[11px] leading-6"><div>{{ $poll->starts_at ? jalali_date($poll->starts_at, 'Y/m/d H:i') : 'شروع آزاد' }}</div><div>{{ $poll->ends_at ? jalali_date($poll->ends_at, 'Y/m/d H:i') : 'بدون پایان' }}</div></div></x-admin.list.td>
                </x-admin.list.tr>
            @empty
                <x-admin.list.empty title="هنوز نظرسنجی ندارید" description="اولین نظرسنجی را ایجاد کنید." action-text="ایجاد نظرسنجی" :action-href="route('admin.polls.create')"/>
            @endforelse
        </x-admin.list.tbody>
    </x-admin.list.table>
    <div class="mt-4"><x-admin.list.pagination :model="$polls"/></div>
@endsection
