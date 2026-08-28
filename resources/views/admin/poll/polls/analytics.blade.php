@extends('admin.layout')
@section('title', 'آمار نظرسنجی')
@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div><h1 class="text-lg font-black text-slate-900">{{ $poll->title }}</h1><p class="mt-1 text-xs text-slate-500">داشبورد زنده نتایج و رفتار مشارکت‌کنندگان</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('admin.polls.edit', $poll) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700">ویرایش</a><a href="{{ route('admin.polls.export', $poll) }}" class="rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-black text-white">خروجی CSV</a></div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        @foreach([
            ['label' => 'کل پاسخ‌ها', 'value' => number_format($poll->responses_count), 'class' => 'text-brand-600'],
            ['label' => 'بازدید یکتای روزانه', 'value' => number_format($poll->views_count), 'class' => 'text-sky-600'],
            ['label' => 'نرخ تبدیل', 'value' => number_format($conversionRate, 1).'٪', 'class' => 'text-emerald-600'],
            ['label' => 'اعضای سایت', 'value' => number_format($registered), 'class' => 'text-violet-600'],
            ['label' => 'مهمانان', 'value' => number_format($guests), 'class' => 'text-amber-600'],
        ] as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-[11px] font-bold text-slate-400">{{ $card['label'] }}</div><div class="mt-2 text-xl font-black {{ $card['class'] }}">{{ $card['value'] }}</div></div>
        @endforeach
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-12">
        <div class="xl:col-span-7"><x-admin.metabox title="روند ۳۰ روز اخیر" :collapsible="false"><div data-poll-timeline='@json($timeline)' class="h-[340px] w-full"></div></x-admin.metabox></div>
        <div class="xl:col-span-5"><x-admin.metabox title="ترکیب پاسخ‌دهندگان" :collapsible="false"><div data-poll-audience='@json(['registered' => $registered, 'guests' => $guests])' class="h-[340px] w-full"></div></x-admin.metabox></div>
    </div>

    <div class="mt-5"><x-admin.metabox title="نتیجه گزینه‌ها" :collapsible="false">
        <div class="space-y-4">
            @forelse($results['options'] as $option)
                <div>
                    <div class="mb-2 flex items-start justify-between gap-4"><div><div class="text-sm font-black text-slate-800">{{ $option['label'] }}</div>@if($option['description'])<div class="mt-1 text-[11px] text-slate-400">{{ $option['description'] }}</div>@endif</div><div class="shrink-0 text-left"><div class="text-sm font-black text-slate-800">{{ number_format($option['count']) }}</div><div class="text-[10px] text-slate-400">{{ number_format($option['respondent_percentage'], 1) }}٪ پاسخ‌دهندگان</div></div></div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full transition-all" style="width: {{ min(100, $option['respondent_percentage']) }}%; background-color: {{ $option['color'] }}"></div></div>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-slate-400">هنوز گزینه‌ای وجود ندارد.</p>
            @endforelse
        </div>
        @if($poll->allows_multiple_selections)<p class="mt-5 rounded-2xl bg-sky-50 px-4 py-3 text-xs leading-6 text-sky-700">در نظرسنجی چندگزینه‌ای درصد هر گزینه نسبت به تعداد پاسخ‌دهندگان محاسبه می‌شود؛ بنابراین مجموع درصدها ممکن است بیشتر از ۱۰۰٪ باشد.</p>@endif
    </x-admin.metabox></div>

    <div class="mt-5"><x-admin.metabox title="آخرین پاسخ‌ها" :collapsible="false">
        <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-right text-xs"><thead><tr class="border-b border-slate-200 text-slate-400"><th class="px-3 py-3">پاسخ‌دهنده</th><th class="px-3 py-3">نوع</th><th class="px-3 py-3">گزینه‌ها</th><th class="px-3 py-3">زمان</th></tr></thead><tbody>
            @forelse($recentResponses as $response)<tr class="border-b border-slate-100 last:border-0"><td class="px-3 py-3 font-bold text-slate-700">{{ $response->user?->display_name ?: 'مهمان ناشناس' }}</td><td class="px-3 py-3"><x-admin.badge :color="$response->user_id ? 'blue' : 'slate'">{{ $response->user_id ? 'عضو #'.$response->user_id : 'مهمان (IP)' }}</x-admin.badge></td><td class="px-3 py-3 text-slate-600">{{ $response->options->pluck('label')->implode('، ') }}</td><td class="px-3 py-3 text-slate-400">{{ jalali_date($response->submitted_at, 'Y/m/d H:i') }}</td></tr>
            @empty<tr><td colspan="4" class="py-10 text-center text-slate-400">هنوز پاسخی ثبت نشده است.</td></tr>@endforelse
        </tbody></table></div>
        <div class="mt-4"><x-admin.list.pagination :model="$recentResponses"/></div>
    </x-admin.metabox></div>
@endsection
