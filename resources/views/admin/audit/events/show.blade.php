@extends('admin.layout')

@section('title', 'جزئیات تغییر #' . $auditEvent->id)

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl font-extrabold text-slate-900">{{ $presenter->typeLabel($auditEvent->type) }} {{ $presenter->subjectTypeLabel($auditEvent->subject_type) }}</h1>
                <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700">رویداد #{{ $auditEvent->id }}</span>
            </div>
            <p class="mt-2 text-sm text-slate-600">{{ $presenter->subjectLabel($auditEvent) }}</p>
        </div>
        <a class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" href="{{ route('admin.audit-events.index') }}">بازگشت به تاریخچه</a>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            <x-admin.metabox title="مقدار قبل و بعد" :collapsible="false">
                @php($changes = $presenter->changes($auditEvent))
                @if($changes)
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-separate border-spacing-0 text-sm">
                            <thead><tr class="text-right text-xs text-slate-500">
                                <th class="border-b border-slate-200 px-3 py-3">فیلد</th>
                                <th class="border-b border-slate-200 px-3 py-3">قبل</th>
                                <th class="border-b border-slate-200 px-3 py-3">بعد</th>
                            </tr></thead>
                            <tbody>
                                @foreach($changes as $change)
                                    <tr class="align-top">
                                        <th class="w-44 border-b border-slate-100 px-3 py-3 text-right font-bold text-slate-800">
                                            {{ $presenter->fieldLabel($change['field']) }}
                                            <span class="mt-1 block break-all text-[11px] font-normal text-slate-400" dir="ltr">{{ $change['field'] }}</span>
                                        </th>
                                        <td class="w-[42%] border-b border-slate-100 px-3 py-3">
                                            @if($change['has_old'])
                                                <pre class="max-h-72 overflow-auto whitespace-pre-wrap break-words rounded-xl bg-rose-50 p-3 text-xs leading-6 text-slate-800" dir="auto">{{ $presenter->formatValue($change['old']) }}</pre>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="w-[42%] border-b border-slate-100 px-3 py-3">
                                            @if($change['has_new'])
                                                <pre class="max-h-72 overflow-auto whitespace-pre-wrap break-words rounded-xl bg-emerald-50 p-3 text-xs leading-6 text-slate-800" dir="auto">{{ $presenter->formatValue($change['new']) }}</pre>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm leading-7 text-slate-500">این رویداد تغییر فیلدی ندارد. جزئیات رابطه یا اطلاعات تکمیلی در بخش کناری آمده است.</p>
                @endif
            </x-admin.metabox>

            @if($auditEvent->meta)
                <x-admin.metabox title="اطلاعات تکمیلی" :collapsible="false">
                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        @foreach($auditEvent->meta as $key => $value)
                            <div class="min-w-0 rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs text-slate-500">{{ ['relation' => 'رابطه', 'related_type' => 'نوع مفهوم مرتبط', 'related_id' => 'شناسه مفهوم مرتبط', 'pivot_key' => 'کلید رابطه'][$key] ?? $presenter->fieldLabel((string) $key) }}</dt>
                                <dd class="mt-1 break-all font-semibold text-slate-800" dir="auto">{{ $key === 'related_type' ? $presenter->subjectTypeLabel((string) $value) : $presenter->formatValue($value) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-admin.metabox>
            @endif
        </div>

        <div class="space-y-4">
            <x-admin.metabox title="مشخصات رویداد" :collapsible="false">
                <dl class="space-y-4 text-sm">
                    <div><dt class="text-slate-500">زمان دقیق (شمسی)</dt><dd class="mt-1 font-bold text-slate-800" dir="ltr">{{ jalali_date($auditEvent->created_at, 'Y/m/d H:i:s') }}</dd></div>
                    <div><dt class="text-slate-500">عامل تغییر</dt><dd class="mt-1 font-bold text-slate-800">{{ $presenter->actorLabel($auditEvent) }} @if($auditEvent->user_id)<span class="text-xs text-slate-400">#{{ $auditEvent->user_id }}</span>@endif</dd></div>
                    <div><dt class="text-slate-500">مفهوم</dt><dd class="mt-1 font-bold text-slate-800">{{ $presenter->subjectTypeLabel($auditEvent->subject_type) }} <span class="break-all text-xs text-slate-500" dir="ltr">#{{ $auditEvent->subject_id }}</span></dd></div>
                    <div><dt class="text-slate-500">نشانی IP</dt><dd class="mt-1 text-slate-800" dir="ltr">{{ $auditEvent->ip_address ?: 'ثبت نشده' }}</dd></div>
                    <div><dt class="text-slate-500">مرورگر یا عامل درخواست</dt><dd class="mt-1 break-all text-xs leading-6 text-slate-700" dir="auto">{{ $auditEvent->user_agent ?: 'ثبت نشده' }}</dd></div>
                </dl>
            </x-admin.metabox>

            <x-admin.metabox title="رویدادهای همین مفهوم" :collapsible="false">
                <a class="mb-3 inline-block text-xs font-bold text-brand-700 hover:underline" href="{{ route('admin.audit-events.index', ['subject_type' => $auditEvent->subject_type, 'subject_id' => $auditEvent->subject_id]) }}">دیدن تمام تغییرات این مفهوم</a>
                <ol class="space-y-2">
                    @foreach($timeline as $item)
                        <li>
                            <a class="flex items-center justify-between gap-2 rounded-xl p-2 text-xs {{ $item->id === $auditEvent->id ? 'bg-brand-50 font-bold text-brand-700' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}" href="{{ route('admin.audit-events.show', $item) }}">
                                <span>{{ $presenter->typeLabel($item->type) }}</span>
                                <time class="whitespace-nowrap" dir="ltr">{{ jalali_date($item->created_at, 'Y/m/d H:i') }}</time>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </x-admin.metabox>
        </div>
    </div>
@endsection
