# اصلاحات امنیتی نشست دانلود و آپدیت لانچر - 2026/08/22

## تغییرات دیتابیس

دو Migration جدید اضافه شده است:

- اتصال `purchase_access_downloads` به `launcher_devices`
- افزودن `signature` و `signature_key_id` به `launcher_releases`

قبل از Migration از دیتابیس نسخه پشتیبان گرفته و ابتدا روی staging اجرا شود.

## نشست دانلود

- نشست محصول، آپدیت و ماد به دستگاه فعال متصل است.
- نشست دستگاه Reset یا غیرفعال‌شده با پاسخ 410 رد می‌شود.
- تعلیق حساب همه نشست‌های محصول و محتوای لانچر را باطل می‌کند.
- عمر پیش‌فرض نشست از ۲۴ ساعت به ۶۰ دقیقه کاهش یافته است.
- تغییر Manifest، نام فایل، اندازه یا SHA-256 نشست قبلی را نامعتبر می‌کند.

## آپدیت امضاشده

پنل «آپدیت لانچر» اکنون علاوه بر SHA-256 دو مقدار می‌گیرد:

- امضای Base64 از نوع RSA-PSS/SHA-256
- شناسه ۶۴ کاراکتری SHA-256 کلید عمومی

امضا و شناسه کلید باید با هم ثبت شوند. کلید خصوصی نباید به سایت یا هاست دانلود
منتقل شود. سایت فقط امضا و شناسه عمومی را نگهداری می‌کند.

## دستور استقرار

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

## تست ضروری

```bash
php artisan test
php artisan route:list --path=api/v1
```

سپس Reset دستگاه، تعلیق/رفع تعلیق، انقضای نشست، تغییر Manifest و نصب یک آپدیت
امضاشده روی staging آزمایش شود. محدودیت استخراج امن فقط در پروژه Updater لانچر
است و روی فایل‌های محصول، ماد یا Sider اعمال نمی‌شود.
