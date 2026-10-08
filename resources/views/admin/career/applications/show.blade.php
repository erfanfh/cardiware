@extends('admin.layout')

@section('title', 'درخواست همکاری #' . $application->id)

@section('content')
    <x-admin.form.validation-errors class="mb-6"/>
    <x-admin.page columns="2">
        <x-slot:sidebar>
            <x-admin.metabox title="بررسی درخواست" :collapsible="false">
                @can('career-application.review')
                    <x-admin.form action="{{ route('admin.career-applications.update', $application) }}" method="put">
                        <div class="space-y-4">
                            <x-admin.field label="وضعیت"><x-admin.form.select name="status" :options="['new' => 'جدید', 'reviewing' => 'در حال بررسی', 'interview' => 'مصاحبه', 'accepted' => 'پذیرفته', 'rejected' => 'رد شده']" :value="old('status', $application->status)"/></x-admin.field>
                            <x-admin.field label="یادداشت داخلی"><x-admin.form.textarea name="internal_notes" rows="7" :value="old('internal_notes', $application->internal_notes)"/></x-admin.field>
                            <x-admin.form.button type="submit" variant="solid">ذخیره بررسی</x-admin.form.button>
                        </div>
                    </x-admin.form>
                @else
                    <p>{{ $application->status }}</p><p class="mt-3 whitespace-pre-line">{{ $application->internal_notes }}</p>
                @endcan
                @if($application->reviewer)<p class="mt-4 text-xs text-slate-500">آخرین بررسی: {{ $application->reviewer->display_name ?: $application->reviewer->username }} · {{ $application->reviewed_at?->format('Y/m/d H:i') }}</p>@endif
                @if($application->viewer)<p class="mt-2 text-xs text-slate-500">اولین مشاهده: {{ $application->viewer->display_name ?: $application->viewer->username }} · {{ $application->viewed_at?->format('Y/m/d H:i') }}</p>@endif
            </x-admin.metabox>
        </x-slot:sidebar>

        <x-admin.metabox title="اطلاعات متقاضی">
            <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500">نام</dt><dd class="font-bold">{{ $application->full_name }}</dd></div>
                <div><dt class="text-slate-500">موقعیت</dt><dd>{{ $application->position_title ?: $application->position?->title ?: 'درخواست عمومی' }}</dd></div>
                <div><dt class="text-slate-500">تلفن</dt><dd dir="ltr" class="text-right">{{ $application->phone }}</dd></div>
                <div><dt class="text-slate-500">ایمیل</dt><dd>{{ $application->email ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">نوع همکاری دلخواه</dt><dd>{{ $application->preferred_cooperation_type ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">تاریخ ثبت</dt><dd>{{ $application->created_at?->format('Y/m/d H:i') }}</dd></div>
                <div><dt class="text-slate-500">نمونه‌کار</dt><dd>@if($application->portfolio_url && preg_match('/^https?:\/\//i', $application->portfolio_url))<a href="{{ $application->portfolio_url }}" target="_blank" rel="noopener noreferrer" class="text-brand-700">مشاهده لینک</a>@else — @endif</dd></div>
                <div><dt class="text-slate-500">رزومه</dt><dd>@if($application->resume_path)<a href="{{ route('admin.career-applications.resume', $application) }}" class="text-brand-700">دانلود فایل</a><span class="mr-2 text-xs text-slate-500" dir="ltr">{{ $application->resume_original_name }}</span>@else — @endif</dd></div>
            </dl>
        </x-admin.metabox>
        <x-admin.metabox title="مهارت‌ها و سابقه"><div class="whitespace-pre-line text-sm leading-8">{{ $application->skills_and_experience }}</div></x-admin.metabox>
    </x-admin.page>
@endsection
