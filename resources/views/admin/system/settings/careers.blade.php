@extends('admin.layout')

@section('title', 'تنظیمات صفحه استخدام')

@php
    $careers = app(\App\Infrastructure\Services\Setting\Groups\CareersSettings::class)->values();
    $sections = [
        'معرفی و Hero' => ['hero_badge' => 'برچسب', 'hero_title' => 'عنوان اصلی', 'hero_description' => 'توضیحات', 'hero_primary_button' => 'دکمه مشاهده موقعیت‌ها', 'hero_secondary_button' => 'دکمه ارسال درخواست'],
        'آمار بالای صفحه' => ['stats_positions_title' => 'عنوان فرصت فعال', 'stats_teams_title' => 'عنوان تیم‌های درگیر', 'stats_cooperation_title' => 'عنوان نوع همکاری', 'stats_cooperation_value' => 'مقدار نوع همکاری', 'stats_review_title' => 'عنوان بررسی رزومه', 'stats_review_value' => 'مقدار بررسی رزومه'],
        'چرا همکاری با ما؟' => ['why_title' => 'عنوان', 'why_subtitle' => 'زیرعنوان'],
        'مزیت‌های همکاری' => ['benefits_title' => 'عنوان', 'benefits_subtitle' => 'زیرعنوان'],
        'موقعیت‌های شغلی' => ['jobs_title' => 'عنوان', 'jobs_subtitle' => 'زیرعنوان', 'jobs_empty_text' => 'متن حالت خالی'],
        'فرآیند و مهارت‌ها' => ['process_title' => 'عنوان فرآیند', 'process_subtitle' => 'زیرعنوان فرآیند', 'skills_title' => 'عنوان مهارت‌ها'],
        'همکاری عمومی' => ['general_title' => 'عنوان کارت', 'general_description' => 'توضیحات کارت', 'general_button' => 'متن دکمه'],
        'فرم درخواست' => ['form_title' => 'عنوان فرم', 'form_subtitle' => 'زیرعنوان فرم', 'form_consent_text' => 'متن تأیید متقاضی', 'tips_title' => 'عنوان نکات', 'review_title' => 'عنوان وضعیت بررسی', 'general_cta_title' => 'عنوان کارت همکاری عمومی', 'general_cta_description' => 'توضیحات کارت همکاری عمومی'],
        'سوالات متداول' => ['faq_title' => 'عنوان', 'faq_subtitle' => 'زیرعنوان'],
    ];
    $repeaters = [
        'why_items' => ['title' => 'کارت‌های چرا همکاری', 'fields' => ['title' => 'عنوان', 'description' => 'توضیح']],
        'benefits_items' => ['title' => 'مزیت‌ها', 'fields' => ['icon' => 'نماد / ایموجی', 'title' => 'عنوان', 'description' => 'توضیح']],
        'process_items' => ['title' => 'مراحل فرآیند', 'fields' => ['title' => 'عنوان', 'description' => 'توضیح']],
        'skills_items' => ['title' => 'مهارت‌ها', 'fields' => ['title' => 'مهارت']],
        'tips_items' => ['title' => 'نکات فرم', 'fields' => ['title' => 'نکته']],
        'review_items' => ['title' => 'راهنمای وضعیت بررسی', 'fields' => ['title' => 'مرحله', 'description' => 'متن وضعیت']],
        'faq_items' => ['title' => 'سوالات متداول', 'fields' => ['title' => 'سوال', 'answer' => 'پاسخ']],
    ];
@endphp

@section('content')
    <x-admin.form action="{{ route('admin.settings.careers.store') }}" method="put">
        <x-admin.page>
            <x-admin.form.validation-errors class="mb-6"/>
            <div class="space-y-6">
                @foreach($sections as $sectionTitle => $fields)
                    <x-admin.metabox :title="$sectionTitle">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            @foreach($fields as $name => $label)
                                <x-admin.field :label="$label" :class="str_contains($name, 'description') ? 'md:col-span-2' : ''">
                                    @if(str_contains($name, 'description'))
                                        <x-admin.form.textarea :name="$name" rows="3" :value="old($name, $careers[$name])"/>
                                    @else
                                        <x-admin.form.input :name="$name" :value="old($name, $careers[$name])"/>
                                    @endif
                                </x-admin.field>
                            @endforeach
                        </div>
                    </x-admin.metabox>
                @endforeach
                @foreach($repeaters as $name => $config)
                    <x-admin.metabox :title="$config['title']">
                        <x-admin.form.repeater :items="old($name, $careers[$name])" :name="$name" :title="$config['title']" :addText="'افزودن ' . $config['title']" :defaultItem="array_fill_keys(array_keys($config['fields']), '')">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                @foreach($config['fields'] as $key => $label)
                                    <x-admin.field :label="$label" :class="in_array($key, ['description', 'answer']) ? 'md:col-span-2' : ''">
                                        @if(in_array($key, ['description', 'answer']))
                                            <x-admin.form.textarea rows="3" x-model="it.{{ $key }}" x-bind:name="field(i, '{{ $key }}')"/>
                                        @else
                                            <x-admin.form.input x-model="it.{{ $key }}" x-bind:name="field(i, '{{ $key }}')"/>
                                        @endif
                                    </x-admin.field>
                                @endforeach
                            </div>
                        </x-admin.form.repeater>
                    </x-admin.metabox>
                @endforeach
            </div>
            <x-admin.form.save-button label="ذخیره تنظیمات"/>
        </x-admin.page>
    </x-admin.form>
@endsection
