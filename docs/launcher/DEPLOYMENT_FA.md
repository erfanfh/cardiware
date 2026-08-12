# راهنمای استقرار روی سایت تست

## ۱. پشتیبان و شاخه

قبل از جایگزینی فایل‌ها از دیتابیس و پروژه پشتیبان بگیرید و تغییرات را ابتدا روی
سایت staging اجرا کنید.

```bash
git checkout -b feature/mw-launcher-backend
composer install --no-interaction
php artisan optimize:clear
```

## ۲. تنظیم `.env`

```dotenv
LAUNCHER_OFFLINE_LICENSE_DAYS=7
LAUNCHER_DEVICE_PROOF_CLOCK_SKEW_SECONDS=300
LAUNCHER_TOKEN_TTL_DAYS=30
LAUNCHER_CHECKOUT_SESSION_TTL_MINUTES=15
LAUNCHER_LATEST_VERSION=1.0.0
LAUNCHER_MINIMUM_SUPPORTED_VERSION=1.0.0
LAUNCHER_DOWNLOAD_URL=https://example.com/downloads/mw-launcher.exe
LAUNCHER_MAINTENANCE_ENABLED=false
LAUNCHER_FORCE_UPDATE=false
LAUNCHER_DOWNLOAD_URL_TTL_MINUTES=10
LAUNCHER_DOWNLOAD_SESSION_TTL_MINUTES=1440
LAUNCHER_ALLOWED_DOWNLOAD_HOSTS=cdn.example.com,files.example.com
```

دامنه‌های واقعی فایل را در `LAUNCHER_ALLOWED_DOWNLOAD_HOSTS` وارد کنید؛ خالی
گذاشتن آن از نظر امنیتی توصیه نمی‌شود.

## ۳. دیتابیس

```bash
php artisan migrate --pretend
php artisan migrate
php artisan db:seed --class=Database\\Seeders\\Identity\\PermissionSeeder
```

مهاجرت جدید جداول device، checkout، download session، update، mod و mod group
را ایجاد و فیلدهای لانچر را به نسخه فروش، فایل و manifest اضافه می‌کند.

پس از Seed، برای مدیران غیر از مدیرکل مجوز «مشاهده و Reset دستگاه‌های لانچر کاربران»
را از صفحه نقش‌ها فعال کنید. مدیرکل به‌صورت خودکار به این بخش دسترسی دارد.

## ۴. کنترل ساخت و تست

```bash
php artisan route:list --path=api/v1
php artisan test
php artisan view:cache
php artisan config:cache
php artisan route:cache
```

اگر `view:cache` خطا داد، استقرار متوقف و خطای Blade اصلاح شود. بعد از موفقیت:

```bash
php artisan queue:restart
```

## ۵. تست پذیرش ضروری

1. ورود دستگاه اول موفق شود؛ دستگاه دوم OTP بخواهد و پس از تأیید، دستگاه اول باطل شود.
2. انتقال دیگری به دستگاه سوم با کد `device_limit_reached` و وضعیت 409 رد شود.
3. حذف دستگاه توسط کاربر با `device_self_service_disabled` رد و Reset پشتیبانی آزمایش شود.
4. پس از یک ورود آنلاین، قطع اینترنت تا ۷ روز مجاز و بعد از آن رد شود.
5. محصول بدون تیک لانچر در Catalog دیده نشود.
6. Half Season و Full Season به‌عنوان دو sale item مستقل دیده شوند.
7. claim محصول رایگان با retry و UUID یکسان، فقط یک دسترسی بسازد.
8. خرید مرورگری پس از پرداخت deep link را باز کند و consume موفق شود.
9. Manifest هیچ URL اصلی دانلودی نداشته باشد.
10. retry دانلود با `download_session_id` یکسان سهمیه را دوباره کم نکند.
11. SHA-256 خراب در لانچر مانع استخراج شود.
12. آپدیت و ماد فقط برای مالک همان sale item قابل دریافت باشند.
11. OPTION FILE پایه و آپدیت در Documents ادغام شود.
12. Sider در محصولی که تیک آن فعال است پیش از بازی اجرا شود.

## ۶. بازگشت

قبل از rollback از داده‌های ایجادشده پشتیبان بگیرید:

```bash
php artisan migrate:rollback --step=1
php artisan optimize:clear
```

Rollback ستون‌ها و جداول جدید را حذف می‌کند و داده‌های مرتبط قابل بازیابی نیستند.
