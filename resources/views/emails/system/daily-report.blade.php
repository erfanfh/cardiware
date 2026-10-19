@extends('emails.layout')

@section('title', 'گزارش روزانه وضعیت سیستم')

@section('content')
    <h1 style="margin:0 0 20px;font-size:20px">گزارش روزانه وضعیت سیستم</h1>
    <p>{{ config('app.name') }} — {{ config('app.url') }}</p>
    <p>بازه گزارش بک‌آپ (به وقت تهران): <span dir="ltr">{{ $report['since'] }}</span> تا <span dir="ltr">{{ $report['until'] }}</span></p>
    <h2 style="font-size:16px">Composer audit</h2>
    <p>{{ $report['composer'] }}</p>
    <h2 style="font-size:16px">npm audit</h2>
    <p>{{ $report['npm'] }}</p>
    <h2 style="font-size:16px">بک‌آپ‌های ۲۴ ساعت گذشته</h2>
    <p>موفق: {{ $report['backups']['succeeded'] }} | ناموفق: {{ $report['backups']['failed'] }}</p>
    @if($report['backups']['succeeded'] + $report['backups']['failed'] === 0)
        <p>هیچ بک‌آپ تکمیل‌شده‌ای در ۲۴ ساعت گذشته ثبت نشده است؛ نیاز به بررسی دارد.</p>
    @endif
    <p>در حال اجرا: {{ $report['backups']['running'] }} | در صف: {{ $report['backups']['queued'] }}</p>
    <p style="color:#64748b;font-size:12px">تعداد در حال اجرا و در صف، شامل بک‌آپ‌های قدیمی‌تر است که هنوز پایان نیافته‌اند.</p>
@endsection
