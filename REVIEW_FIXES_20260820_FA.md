# اصلاحات بازبینی API لانچر — ۲۰۲۶/۰۸/۲۰

این نسخه باید هم‌زمان با لانچر Revision محور استقرار پیدا کند.

## قرارداد مانیفست

- هر تغییر مؤثر در فایل محصول، آپدیت یا ماد، `manifest_revision` محصول را افزایش می‌دهد.
- `manifest_id` از نسخه محصول و Revision جاری ساخته می‌شود.
- ایجاد، ویرایش یا حذف فایل، نشست‌های Prepare قبلی و لینک‌های امضاشده قدیمی را باطل می‌کند.
- پاسخ‌های Catalog، Library و Manifest با `no-store` برگردانده می‌شوند تا داده قدیمی در کش HTTP باقی نماند.
- درخواست Prepare باید این مقادیر را ارسال کند: `download_session_id`، `manifest_revision`، `file_name`، `size_bytes` و `sha256`.
- اگر هر مقدار با فایل جاری متفاوت باشد، پاسخ 409 صادر می‌شود و نشست قبلی قابل استفاده نیست.
- لینک دانلود امضاشده Revision را حمل می‌کند؛ Revision قدیمی پاسخ 410 دریافت می‌کند.
- نشست دانلود snapshot نام فایل، حجم، SHA-256 و Revision را نگه می‌دارد و قبل از Stream دوباره کنترل می‌شود.
- رمز آرشیو با Cast رمزنگاری‌شده نگهداری و فقط از endpoint محافظت‌شده مالک محصول تحویل داده می‌شود.

## استقرار

از ریشه Laravel، کنار فایل `artisan`:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan test --filter=ManifestRevisionInvalidationTest
```

سپس Workerهای صف و PHP-FPM/Octane را طبق محیط سرور Restart کنید تا کد و Observerهای جدید بارگذاری شوند.

## آزمایش پذیرش

1. مانیفست محصول را دریافت و یک لینک Prepare بسازید.
2. نام، URL، حجم، SHA-256، ترتیب یا تعداد پارت‌ها را تغییر دهید.
3. بررسی کنید Revision دقیقاً افزایش یافته و نشست قبلی `invalidated_at` گرفته است.
4. لینک امضاشده قدیمی باید 410 و Prepare با اطلاعات قبلی باید 409 بدهد.
5. Prepare جدید باید نام، حجم، SHA-256 و Revision جاری را برگرداند.
6. دانلود کاربر فاقد PurchaseAccess باید 403 باشد.
7. مقدار رمز آرشیو نباید در Log، پاسخ عمومی Catalog یا فایل JSON عمومی ظاهر شود.
