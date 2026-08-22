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
LAUNCHER_DOWNLOAD_URL=https://example.com/downloads/MWLauncher-1.20.0.zip
LAUNCHER_UPDATE_FILE_NAME=MWLauncher-1.20.0.zip
LAUNCHER_UPDATE_FILE_SIZE_BYTES=12345678
LAUNCHER_UPDATE_SHA256=64_character_sha256_here
LAUNCHER_UPDATE_SIGNATURE=base64_rsa_pss_signature_here
LAUNCHER_UPDATE_SIGNATURE_KEY_ID=64_character_public_key_id_here
LAUNCHER_MAINTENANCE_ENABLED=false
LAUNCHER_FORCE_UPDATE=false
LAUNCHER_DOWNLOAD_URL_TTL_MINUTES=60
LAUNCHER_DOWNLOAD_SESSION_LEASE_MINUTES=10080
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

مهاجرت‌های نسخه ۲۰ نیز رمز رمزنگاری‌شده بسته‌های پایه/آپدیت/ماد، جدول
`launcher_releases`، جدول `launcher_slides` و جداول اعلانات و راهنمای لانچر را اضافه می‌کنند. رمز هر 7z از
ویرایش همان محصول ثبت می‌شود.

پس از Seed، برای مدیران غیر از مدیرکل مجوز «مشاهده و Reset دستگاه‌های لانچر کاربران»
را از صفحه نقش‌ها فعال کنید. مدیرکل به‌صورت خودکار به این بخش دسترسی دارد.
همین مجوز، منوهای «آپدیت لانچر»، «اسلایدر لانچر»، «اعلانات لانچر» و «راهنمای لانچر» را نیز در پنل مدیریت نمایش می‌دهد.

## ۴. کنترل ساخت و تست

```bash
php artisan route:list --path=api/v1
php artisan test
php artisan view:cache
php artisan config:cache
php artisan route:cache
```

ورود پیامکی لانچر از دو مسیر `auth/otp/request` و `auth/otp/verify` استفاده می‌کند و همان
تنظیمات OTP و سرویس پیامک سایت را به کار می‌گیرد. مسیر جمع `mods` مسیر اصلی دانلود ماد است؛
مسیر مفرد `mod` فقط برای سازگاری موقت با نسخه‌های قدیمی لانچر نگه داشته شده است.

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
13. یک بسته 7z رمزدار با رمز صحیح و سپس رمز اشتباه آزمایش شود.
14. نسخه جدید لانچر در پنل ثبت و دانلود Resume، SHA-256، نصب و Rollback آزمایش شود.
15. حداقل دو اسلاید فعال ثبت شود؛ تغییر خودکار، ترتیب، لینک داخلی محصول و لینک HTTPS آزمایش شود.
16. پس از یک بار نمایش آنلاین اسلایدها، اینترنت قطع و نمایش تصاویر کش‌شده بررسی شود.

## ۶. انتشار آپدیت لانچر

فایل خروجی `publish-update.ps1` پروژه لانچر را روی هاست یا CDN آپلود کنید. سپس از
منوی «آپدیت لانچر»، نسخه، نام فایل، حجم دقیق بایت، لینک مستقیم HTTPS، SHA-256،
امضای RSA-PSS و شناسه کلید عمومی را ثبت کنید. تیک «آپدیت اجباری» ورود نسخه‌های
قدیمی را تا نصب نسخه جدید مسدود می‌کند.

کلید خصوصی امضای آپدیت نباید روی هاست عمومی، CDN، مخزن Git یا داخل ZIP پروژه
قرار گیرد. فقط فایل عمومی `update-signing-public.pem` همراه لانچر منتشر می‌شود.
مراحل دقیق ساخت و امضای بسته در `docs/SECURE-UPDATE-AND-DOWNLOAD-1.21.8-FA.md`
پروژه لانچر نوشته شده است.

URL امضاشده دانلود محصول، آپدیت و ماد ۶۰ دقیقه اعتبار دارد و پس از آن لانچر با
درخواست Prepare یک URL تازه می‌گیرد. نشست سمت سرور یک Lease هفت‌روزه و قابل تمدید
است؛ هر Prepare معتبر ابتدا خرید، دستگاه فعال، revision، نام، حجم و SHA-256 را دوباره
کنترل و سپس همان نشست را بدون مصرف دوباره سهمیه تمدید می‌کند. بنابراین دانلودهای
چندده‌گیگابایتی می‌توانند بعد از قطع اینترنت یا توقف طولانی با Range از همان بایت قبلی
ادامه پیدا کنند. فقط کد `launcher_manifest_changed` یا ناهماهنگی واقعی نام/حجم/هش
اجازه حذف فایل ناقص و شروع از صفر را می‌دهد. Reset کامل دستگاه یا تعلیق حساب همچنان
نشست را فوراً و غیرقابل تمدید باطل می‌کند.

کلید قدیمی `LAUNCHER_DOWNLOAD_SESSION_TTL_MINUTES` دیگر استفاده نمی‌شود. روی سرور
حتماً `LAUNCHER_DOWNLOAD_SESSION_LEASE_MINUTES` را تنظیم کنید و سپس اجرا کنید:

```bash
php artisan optimize:clear
php artisan config:cache
```

محدودیت تعداد فایل و حجم استخراج فقط مربوط به ZIP آپدیت خود لانچر است. هیچ سقف
تعداد فایلی برای بسته‌های بازی، ماد یا Sider در این تغییر اضافه نشده است.

نسخه `1.20.0` زیرساخت آپدیتر را برای اولین بار اضافه می‌کند و باید یک‌بار با روش فعلی
توزیع شود؛ انتشار نسخه‌های بعدی از همین پنل انجام خواهد شد.

## ۷. مدیریت اسلایدر صفحه اصلی

از منوی «اسلایدر لانچر» تصویر، ترتیب و بازه زمانی نمایش را ثبت کنید. عنوان، توضیح
و متن دکمه اختیاری هستند و برای اسلاید تمام‌تصویر می‌توان هر سه را خالی گذاشت.
مقصد هر اسلاید می‌تواند یکی از این سه حالت باشد:

- صفحه یک محصول منتشرشده در خود لانچر
- لینک مستقیم HTTPS سایت
- بدون لینک و بدون دکمه

لانچر اسلایدها را از `GET /api/v1/launcher/slides` دریافت می‌کند و تصاویر موفق را
در پوشه LocalAppData کاربر کش می‌کند؛ بنابراین آخرین اسلایدهای دریافت‌شده در حالت
آفلاین مجاز هفت‌روزه نیز قابل نمایش هستند.

## ۸. اعلانات و راهنمای لانچر

- در «اعلانات لانچر» عنوان، متن، نوع رنگی، لینک اختیاری و بازه انتشار ثبت می‌شود.
- تعداد اعلان‌های خوانده‌نشده برای هر کاربر جداگانه نگهداری و روی منوی لانچر نمایش داده می‌شود.
- در «راهنمای لانچر» عنوان، خلاصه، متن کامل و ترتیب نمایش قابل مدیریت است.
- آخرین اعلانات و راهنماهای دریافت‌شده در کش رمزنگاری‌شده کاربر باقی می‌مانند تا در حالت آفلاین مجاز نیز دیده شوند.
- مسیرهای API جدید: `GET launcher/notifications`، `POST launcher/notifications/read` و `GET launcher/guides`.

## ۹. بازگشت

قبل از rollback از داده‌های ایجادشده پشتیبان بگیرید:

```bash
php artisan migrate:rollback --step=1
php artisan optimize:clear
```

Rollback ستون‌ها و جداول جدید را حذف می‌کند و داده‌های مرتبط قابل بازیابی نیستند.
