گزارش روزانه وضعیت سیستم | {{ config('app.name') }}
{{ config('app.url') }}
بازه گزارش بک‌آپ (به وقت تهران): {{ $report['since'] }} تا {{ $report['until'] }}

Composer audit: {{ $report['composer'] }}
npm audit: {{ $report['npm'] }}

بک‌آپ‌های ۲۴ ساعت گذشته:
موفق: {{ $report['backups']['succeeded'] }}
ناموفق: {{ $report['backups']['failed'] }}
@if($report['backups']['succeeded'] + $report['backups']['failed'] === 0)
هیچ بک‌آپ تکمیل‌شده‌ای در ۲۴ ساعت گذشته ثبت نشده است؛ نیاز به بررسی دارد.
@endif
در حال اجرا: {{ $report['backups']['running'] }}
در صف: {{ $report['backups']['queued'] }}
تعداد در حال اجرا و در صف، شامل بک‌آپ‌های قدیمی‌تر است که هنوز پایان نیافته‌اند.
