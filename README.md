# ModdingWay | مودینگ‌وی

**پلتفرم فارسی مدیریت محتوای بازی، فروش محصولات دیجیتال و ارائه خدمات MW Launcher.**

مودینگ‌وی یک برنامه Laravel با معماری یکپارچه و ماژولار است. وب‌سایت عمومی، پنل مدیریت، حساب کاربری، فروشگاه و API نسخه‌بندی‌شده لانچر در همین پروژه قرار دارند. رابط کاربری با Blade، Livewire و Tailwind CSS ساخته شده و امکانات نمایش راست‌به‌چپ، ترجمه فارسی و تاریخ شمسی دارد.

این مخزن شامل بک‌اند و صفحات وب مرتبط با **MW Launcher** است؛ سورس برنامه دسکتاپ ویندوزی لانچر در این مخزن قرار ندارد.

## فهرست راهنما

- [قابلیت‌ها و ماژول‌ها](#features)
- [فناوری‌ها و پیش‌نیازها](#requirements)
- [نصب و راه‌اندازی](#installation)
- [ساخت اولین مدیر](#first-admin)
- [اجرای محیط توسعه](#development)
- [ساختار و معماری](#architecture)
- [تنظیمات و سرویس‌ها](#configuration)
- [صف‌ها و زمان‌بندی](#queues)
- [API و اتصال لانچر](#api)
- [تست و کنترل کیفیت](#testing)
- [استقرار و نگهداری](#deployment)
- [مهاجرت وردپرس](#wordpress)
- [رفع اشکال](#troubleshooting)
- [مستندات تکمیلی](#documentation)
- [مجوز](#license)

<a id="features"></a>

## قابلیت‌ها و ماژول‌ها

| حوزه | ماژول‌ها | امکانات اصلی |
| --- | --- | --- |
| هویت و دسترسی | `Identity`، `Security` | ورود با رمز یا OTP، نقش و مجوز، وضعیت حساب، اعلان ورود و ثبت رویدادهای سوءاستفاده |
| محتوا و تعامل | `Content`، `Project`، `Video`، `Discussion`، `Poll` | نوشته و برگه، پروژه و نقشه راه، ویدئو، دیدگاه و واکنش، نظرسنجی |
| کاتالوگ و فروش | `Catalog`، `Marketplace`، `Commerce` | محصول، نسخه فروش، ویژگی و افزونه، فروشنده، سبد خرید، سفارش، بازپرداخت و دسترسی خرید |
| مالی و تخفیف | `Financial`، `Promotion` | درگاه پرداخت، کیف پول، شارژ و انتقال، برداشت، رزرو موجودی و کوپن قابل ترکیب |
| اشتراک و مشارکت | `Vip`، `Loyalty`، `Raffle` | طرح و اشتراک VIP، امتیاز و پاداش، تبدیل امتیاز، قرعه‌کشی، بلیت، برنده و تأیید جایزه |
| لانچر و دانلود | `Launcher`، `Download` | کتابخانه، دستگاه، نشست دانلود، فایل پایه و آپدیت و ماد، انتشار لانچر و آمار کلیک دانلود سایت |
| پشتیبانی و اطلاع‌رسانی | `Support`، `Notification`، `Communication` | تیکت و پیوست، دپارتمان، اعلان داخل سایت، پیامک و ایمیل |
| نمایش و کشف محتوا | `Layout`، `Navigation`، `Taxonomy`، `Feature`، `Seo`، `Slug` | چیدمان، منو، دسته و برچسب، نشان‌های محتوا، متادیتا و Schema، نقشه سایت و ریدایرکت |
| مدیریت و زیرساخت | `Media`، `Icon`، `System`، `Audit` | کتابخانه رسانه و نسخه‌های تصویر، آیکن، تنظیمات، بازدید، بک‌آپ و تاریخچه تغییرات |
| سازمان و انتقال داده | `Organization`، `Career`، `WordPressMigration` | اعضای تیم، فرصت شغلی و درخواست همکاری، ابزار انتقال داده از وردپرس |

<a id="requirements"></a>

## فناوری‌ها و پیش‌نیازها

نسخه‌های PHP packageها در جدول از `composer.lock` خوانده شده‌اند. محدوده‌های سازگاری در manifestها تعریف می‌شوند؛ نصب تکرارپذیر باید با lock fileها انجام شود.

| بخش | نسخه یا نیاز پروژه |
| --- | --- |
| PHP | **8.3 یا بالاتر** برای وابستگی‌های فعلی؛ مقدار `config.platform.php` برابر `8.3.6` است |
| Laravel | `12.63.0`؛ محدوده `^12.0` |
| Livewire | `4.2.4`؛ محدوده `^4.0` |
| Composer | نسخه 2 با Runtime API سازگار با `^2.2` |
| Node.js | شاخه 20 از نسخه `20.19.0` یا نسخه `22.12.0` و بالاتر، مطابق نیاز Vite 7 |
| رابط کاربری | Blade، Tailwind CSS 4، Vite 7، Alpine.js و Swiper |
| ابزارهای مدیریت | TinyMCE، Tom Select، SortableJS، Pickr و ECharts |
| دیتابیس اصلی | MySQL / MariaDB؛ اتصال فایل نمونه `mysql` است |
| کش و صف | Redis، Predis و Laravel Horizon `5.47.2` |
| API | Laravel Sanctum و Scribe `5.11.0` |
| پشتیبان‌گیری | Spatie Laravel Backup `10.3.2` و فضای S3-compatible |
| تست | PHPUnit 11 و Node.js Test Runner |

> الزام مستقیم PHP در `composer.json` برابر `^8.2` است، اما وابستگی‌های قفل‌شده بک‌آپ به PHP `^8.3` نیاز دارند. runtime واقعی را با `composer check-platform-reqs` بررسی کنید؛ مقدار `config.platform` جای نصب PHP مناسب را نمی‌گیرد.

افزونه‌های موردنیاز را با Composer کنترل کنید؛ از جمله `pdo` و درایور دیتابیس، `mbstring`، `fileinfo`، `openssl`، `dom`، `simplexml`، `zip` و برای Horizon افزونه‌های **`pcntl` و `posix`**. پردازش تصویر به GD با پشتیبانی WebP و تست‌های SQLite به `pdo_sqlite` نیاز دارند.

**محیط اجرای کامل، Linux یا WSL2 است.** نصب عادی Horizon روی PHP بومی ویندوز به علت نبود `pcntl` و `posix` کامل نمی‌شود. بخشی از Scheduler نیز از `nice` و `ionice` لینوکس استفاده می‌کند. وجود Sail در وابستگی‌ها به معنی آماده‌بودن فایل Docker Compose در این مخزن نیست.

<a id="installation"></a>

## نصب و راه‌اندازی

دستورها را از ریشه پروژه اجرا کنید. این مراحل برای **نصب تازه روی دیتابیس خالی** هستند.

### ۱. نصب وابستگی‌ها

```bash
composer install
npm ci
```

برای نصب معمول از `composer install` استفاده کنید تا نسخه‌های قفل‌شده حفظ شوند. `composer update` نسخه وابستگی‌ها را تغییر می‌دهد.

### ۲. آماده‌سازی محیط

اگر `.env` وجود ندارد، آن را از فایل نمونه بسازید:

```bash
php -r "file_exists('.env') || copy('.env.example', '.env');"
```

یک دیتابیس MySQL یا MariaDB خالی با پشتیبانی `utf8mb4` بسازید و اتصال واقعی را در `.env` قرار دهید. نمونه تنظیمات توسعه:

```dotenv
APP_NAME=ModdingWay
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_LOCALE=fa
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=moddingway
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

CACHE_STORE=redis
SESSION_DRIVER=database
QUEUE_CONNECTION=redis

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

MAIL_MAILER=log
```

نام دیتابیس بالا باید با دیتابیس ساخته‌شده یکسان باشد؛ مقدار فعلی `.env.example` برابر `moddingwayy` است. زمان برنامه در `config/app.php` روی `Asia/Tehran` تنظیم شده است.

**Redis را پیش از مراحل بعد روشن کنید.** کد مجوزها و چند بخش دیگر از `Cache::tags()` استفاده می‌کنند؛ مقدار پیش‌فرض `CACHE_STORE=database` در فایل نمونه برای اجرای کامل مناسب نیست. تنظیمات بالا این تفاوت را اصلاح می‌کند.

فقط در نصب تازه با `APP_KEY` خالی، کلید برنامه را تولید کنید:

```bash
php artisan key:generate
php artisan config:clear
```

کلید نصب موجود را حفظ کنید؛ داده‌های رمز‌شده به آن وابسته‌اند.

### ۳. جداول و داده‌های پایه

```bash
php artisan migrate
php artisan db:seed
php artisan icons:import
php artisan storage:link
npm run build
```

Seeder اصلی نقش‌ها، مجوزها، چیدمان صفحات، تعریف ویژگی‌ها و منوی اولیه را ایجاد می‌کند. **کاربر مدیر، محصول، سفارش یا محتوای نمایشی ساخته نمی‌شود.** آیکن‌ها از `resources/icon-sets` وارد می‌شوند. لینک storage دسترسی به فایل‌های disk عمومی را فراهم می‌کند.

> `db:seed` را بدون بررسی روی داده موجود تکرار نکنید. برای نمونه، `NavigationMenuSeeder` منوهای خارج از فهرست خودش را حذف می‌کند و `PageLayoutSeeder` چیدمان‌های تعریف‌شده را همگام می‌کند.

دستور `settings:sync-defaults` اختیاری است و مقادیر ثابت نوشته‌شده در کد را **بازنویسی** می‌کند؛ آن را جزو مراحل روزمره استقرار قرار ندهید. تنظیمات عمومی، پیامک، پرداخت و ظاهر را پس از ایجاد مدیر از پنل تکمیل کنید.

<a id="first-admin"></a>

## ساخت اولین مدیر

پس از seed، وارد Tinker شوید:

```bash
php artisan tinker
```

در نشست Tinker، اطلاعات نمونه زیر را با اطلاعات خود جایگزین کنید. شماره موبایل باید متعلق به مدیر، یکتا و با قالب `09xxxxxxxxx` باشد؛ ورود فعلی وب با موبایل آغاز می‌شود.

```php
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;

$role = Role::query()->where('is_super_admin', true)->firstOrFail();

User::query()->create([
    'username' => 'site-admin',
    'first_name' => 'Site',
    'last_name' => 'Admin',
    'display_name' => 'مدیر سایت',
    'email' => 'admin@example.test',
    'mobile' => 'REPLACE_WITH_YOUR_11_DIGIT_MOBILE',
    'role_id' => $role->id,
    'status' => 'active',
    'mobile_verified_at' => now(),
    'password' => 'REPLACE_WITH_A_UNIQUE_STRONG_PASSWORD',
]);
```

مدل `User` رمز را با cast نوع `hashed` ذخیره می‌کند. این مثال را یک‌بار برای نصب تازه اجرا کنید و نام کاربری، ایمیل، موبایل و رمز placeholder را پیش از اجرا تغییر دهید.

از `/auth/` شماره موبایل ثبت‌شده را وارد کنید و سپس با رمز وارد شوید؛ پنل در `/admin` است. برای ورود پیامکی یا ثبت‌نام کاربران جدید، ارائه‌دهنده و قالب OTP را جداگانه تنظیم کنید.

<a id="development"></a>

## اجرای محیط توسعه

پس از نصب و روشن‌بودن Redis و دیتابیس:

```bash
composer run dev
```

این اسکریپت وب‌سرور Laravel، شنونده صف پیش‌فرض با یک تلاش و Vite را هم‌زمان اجرا می‌کند. اجرای جداگانه در ترمینال‌های مستقل:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

آدرس معمول برنامه `http://127.0.0.1:8000` است. در صورت تغییر دامنه یا پورت، `APP_URL` را هماهنگ کنید.

| مسیر | کاربرد |
| --- | --- |
| `/` | صفحه اصلی |
| `/auth/` | ورود و ثبت‌نام |
| `/account/dashboard` | حساب کاربر واردشده |
| `/admin` | پنل مدیر با مجوز `admin.display` |
| `/launcher` | معرفی و دانلود عمومی لانچر |
| `/api/v1` | پیشوند مسیرهای API؛ صفحه مستقل نیست |
| `/docs/api` | مستندات تولیدشده API با مجوز `admin.api-docs` |
| `/up` | بررسی سلامت پایه Laravel |

مسیر `/up` جای پایش دیتابیس، Redis و workerها را نمی‌گیرد. برای زمان‌بندی در توسعه، ترمینال دیگری باز کنید:

```bash
php artisan schedule:work
```

`composer run dev` صف‌های اختصاصی قرعه‌کشی، بررسی فایل و بک‌آپ را مصرف نمی‌کند. برای آزمایش این قابلیت‌ها، Horizon یا workerهای اختصاصی بخش صف‌ها را نیز اجرا کنید.

اسکریپت `composer run setup` هم وجود دارد، اما کلید را تولید و migration را با `--force` اجرا می‌کند، از `npm install` استفاده می‌کند و seed، ورود آیکن و لینک storage را انجام نمی‌دهد؛ مراحل صریح نصب، کنترل بیشتری بر راه‌اندازی می‌دهند.

<a id="architecture"></a>

## ساختار و معماری

پروژه یک **Modular Monolith** با تفکیک بر اساس دامنه است. مدل‌ها و قواعد هر قابلیت در Domain، عملیات در Application و رابط HTTP یا Livewire در لایه نمایش قرار می‌گیرند.

```text
app/
├── Domain/                 مدل‌ها، enumها، قراردادها و policyها
├── Application/            actionها، queryها، DTOها و سرویس‌های کاربردی
├── Http/                   controllerها، requestها، resourceها و middlewareها
├── Livewire/               کامپوننت‌های تعاملی Admin و Front
├── Infrastructure/         تنظیمات، سرویس پیامک و ایمیل، کش و ابزارهای مشترک
├── Jobs/                   کارهای پس‌زمینه مشترک
├── Console/Commands/       فرمان‌های عملیاتی و ابزارهای انتقال
├── Providers/              ثبت سرویس، رویداد، مجوز و زمان‌بندی
├── Rules/                  قواعد اعتبارسنجی
└── View/                   کامپوننت‌های نمایشی PHP

bootstrap/                  راه‌اندازی برنامه، مسیرها و providerها
config/                     تنظیمات زیرساخت و قابلیت‌ها
database/                   migrationها، factoryها و seederها
resources/
├── views/                  قالب‌های Blade عمومی و مدیریت
├── js/                     ورودی‌ها و ماژول‌های Front و Admin
├── css/                    استایل‌ها
├── lang/                   ترجمه‌های فارسی
└── icon-sets/              مجموعه‌های JSON آیکن
routes/
├── front.php + front/      مسیرهای عمومی و حساب کاربری
├── admin.php + admin/      مسیرهای پنل مدیریت
├── api.php + api/v1/       API نسخه اول
└── preview.php             پیش‌نمایش
public/                     document root، فایل عمومی و خروجی build
storage/                    فایل‌های برنامه، لاگ و cacheهای runtime
tests/                      Unit، Feature، JavaScript و نمونه مرورگری
docs/                       راهنماهای تخصصی و عملیاتی
```

ثبت مسیرها در `bootstrap/app.php` انجام می‌شود؛ پروژه به `routes/web.php` یا `routes/console.php` متکی نیست. مسیرهای محتوا در انتهای `routes/front.php` بارگذاری می‌شوند تا با مسیرهای مشخص‌تر تداخل نکنند.

برای افزودن قابلیت، دامنه مناسب را انتخاب کنید، عملیات را در Application قرار دهید و controller یا Livewire و مسیر همان دامنه را اضافه کنید. [سند معماری](docs/architecture.md) قراردادهای توسعه را توضیح می‌دهد؛ جدول فناوری آن قدیمی است و نسخه‌های فعلی را باید از lock fileها خواند.

<a id="configuration"></a>

## تنظیمات و سرویس‌ها

تنظیمات دو منبع دارند: فایل‌های config و متغیرهای محیطی برای زیرساخت؛ جدول تنظیمات و پنل برای رفتار تجاری و محتوای قابل ویرایش. گروه‌ها در `app/Infrastructure/Services/Setting/Groups` قرار دارند.

| بخش | مرجع و نکته اجرا |
| --- | --- |
| دیتابیس، کش، session و صف | `.env` و فایل‌های متناظر در `config/` |
| پیامک و OTP | تنظیمات عمومی پنل؛ پیاده‌سازی SMS.ir، فراز اس‌ام‌اس و ملی‌پیامک در `Infrastructure/Services/Sms/Providers` |
| ایمیل | `MAIL_*` و `config/mail.php`؛ در توسعه `MAIL_MAILER=log` ایمیل را در لاگ ثبت می‌کند |
| پرداخت | تنظیمات عمومی پنل و `config/financial.php`؛ زیبال، زرین‌پال، پی‌پینگ و آقای پرداخت |
| فایل و Object Storage | `config/filesystems.php`؛ فایل عمومی از مسیر `public/storage` |
| لانچر | `config/launcher.php`، `config/launcher_security.php` و صفحات محصول و انتشار |
| بک‌آپ | `config/backup.php`، تنظیمات پنل، `PARSPACK_BACKUP_*` و `BACKUP_ARCHIVE_PASSWORD` |
| SEO و نقشه سایت | پنل SEO، `config/seo-sitemap.php` و فرمان‌های `sitemap:*` |

مبالغ داخلی ماژول مالی برحسب **تومان** هستند؛ تبدیل لازم برای درگاه در پیکربندی و پیاده‌سازی آن اعمال می‌شود.

بک‌آپ رمزگذاری‌شده به رمز آرشیو، فضای خصوصی S3-compatible، افزونه ZIP، ابزار `mysqldump` برای MySQL و فضای موقت کافی نیاز دارد. صف واقعی آن در کد فعلی `backups` روی connection اختصاصی `redis-backup` است؛ [راهنمای بک‌آپ](docs/backup-operations-fa.md) روش فعال‌سازی و آزمون بازیابی را توضیح می‌دهد.

`.env`، `APP_KEY`، اعتبارنامه‌ها و کلیدهای خصوصی امضا را وارد Git نکنید. همه متغیرهای اختیاری لانچر و وردپرس در `.env.example` فهرست نشده‌اند؛ فایل config مربوط، مرجع نام و مقدار پیش‌فرض آن‌هاست.

<a id="queues"></a>

## صف‌ها و زمان‌بندی

### صف‌های پس‌زمینه

چهار supervisor در `config/horizon.php` تعریف شده‌اند:

| connection | queue | کاربرد | timeout پیش‌فرض Horizon |
| --- | --- | --- | --- |
| `redis` | `default` | کارهای عمومی، اعلان و listenerهای صفی | ۶۰ ثانیه |
| `redis-raffle` | `raffle` | اجرای قرعه‌کشی | ۳۶۰۰ ثانیه |
| `redis-download-integrity` | `download-integrity` | بررسی مشخصات فایل دانلود لانچر | ۱۸۰۰ ثانیه |
| `redis-backup` | `backups` | اجرای بک‌آپ | ۷۳۰۰ ثانیه |

برای اجرای supervisorها روی محیط سازگار:

```bash
php artisan horizon
```

در سرور، Horizon باید زیر نظر process manager مانند Supervisor یا systemd اجرا شود. در صورت استفاده از worker مستقل، هر خط زیر یک پردازش جداست:

```bash
php artisan queue:work redis --queue=default --tries=1 --timeout=60
php artisan queue:work redis-raffle --queue=raffle --tries=1 --timeout=3600
php artisan queue:work redis-download-integrity --queue=download-integrity --tries=1 --timeout=1800
php artisan queue:work redis-backup --queue=backups --tries=1 --timeout=7300
```

یکی از دو روش Horizon یا workerهای مستقل را برای مدیریت این صف‌ها انتخاب کنید. `retry_after` هر connection باید از زمان اجرای مجاز worker بیشتر بماند؛ مقادیر پیش‌فرض در `config/queue.php` تنظیم شده‌اند. تغییر `QUEUE_CONNECTION`، connection صریح jobهای اختصاصی را تغییر نمی‌دهد.

### Laravel Scheduler

زمان‌بندی‌ها در `bootstrap/app.php` و `app/Providers/SchedulerServiceProvider.php` ثبت می‌شوند:

| کار | تناوب پیش‌فرض |
| --- | --- |
| انتقال بازدید و کلیک دانلود از Redis به SQL | هر دقیقه؛ قابل تنظیم |
| انقضای رزرو موجودی کیف پول و همگام‌سازی وضعیت قرعه‌کشی | هر دقیقه |
| انقضای رزرو کوپن، مهلت برندگان و پیامک یادآوری | هر ۵ دقیقه |
| بررسی نیاز به بازسازی نقشه سایت | هر ۵ دقیقه؛ با ابزارهای Linux |
| بررسی موعد بک‌آپ خودکار | هر ۱۵ دقیقه؛ اجرای بک‌آپ تابع تنظیمات پنل است |
| بستن تیکت بی‌فعالیت | هر ساعت در دقیقه ۱۰ |
| پاک‌سازی سبدها، پیوست تیکت، بک‌آپ و رویداد امنیتی قدیمی | روزانه |
| به‌روزرسانی بازه‌های IP ایران برای لانچر | جمعه ساعت ۰۴:۵۰ |

زمان‌ها به وقت تهران هستند. فهرست دقیق را با دستور زیر ببینید:

```bash
php artisan schedule:list
```

روی Linux، cron هر دقیقه اجرا شود؛ مسیر نمونه را جایگزین کنید:

```cron
* * * * * cd /path/to/moddingway && php artisan schedule:run >> /dev/null 2>&1
```

در استقرار چندسروری، cache مشترک برای قفل‌های `onOneServer()` و `withoutOverlapping()` لازم است.

<a id="api"></a>

## API و اتصال لانچر

API زیر `/api/v1` ثبت می‌شود. مسیرهای محافظت‌شده از Sanctum، وضعیت حساب، دستگاه فعال و در برخی موارد ability توکن استفاده می‌کنند؛ bootstrap و کاتالوگ عمومی قواعد متفاوتی دارند.

| مسیر نمونه | کاربرد |
| --- | --- |
| `GET /api/v1/launcher/bootstrap` | پیکربندی اولیه و اطلاعات انتشار |
| `POST /api/v1/auth/login` | ورود با رمز |
| `POST /api/v1/auth/otp/request` و `POST /api/v1/auth/otp/verify` | ورود یا تأیید دستگاه با OTP |
| `GET /api/v1/me` | کاربر فعلی |
| `GET /api/v1/products` | کاتالوگ عمومی |
| `GET /api/v1/launcher/catalog` | نسخه‌های فروش مخصوص لانچر |
| `GET /api/v1/library` | دسترسی‌های خرید کاربر |
| `GET /api/v1/library/{purchaseAccess}/manifest` | اطلاعات نصب و فایل‌های نسخه خریداری‌شده |
| `POST /api/v1/library/{purchaseAccess}/files/{file}/download` | آماده‌سازی دانلود فایل پایه |
| `GET /api/v1/polls/{pollSlug}` | دریافت نظرسنجی |

این جدول گزیده است. برای مشاهده مسیرها و تولید HTML، OpenAPI و Postman:

```bash
php artisan route:list --path=api/v1
php artisan scribe:generate
```

پس از تولید، `/docs/api` برای کاربر واردشده دارای مجوز `admin.api-docs` در دسترس است. خروجی‌ها و دارایی‌های Scribe در `.gitignore` هستند و باید در محیط مقصد یا فرایند build تولید شوند.

نکات اتصال و تنظیم لانچر:

- واحد مستقل مالکیت و تنظیمات، `ProductSaleItem` است؛ هر نسخه فروش manifest، فایل، آپدیت و ماد خودش را دارد.
- `LAUNCHER_PUBLIC_DOWNLOAD_URL` برای نصب‌کننده عمومی است؛ `LAUNCHER_DOWNLOAD_URL` مربوط به بسته آپدیت خود لانچر است. مقدار نصب‌کننده در پنل بر fallback محیطی اولویت دارد.
- سیاست فعلی `launcher_security.php` یک دستگاه فعال و یک انتقال را تعریف می‌کند؛ مقدار قدیمی `LAUNCHER_MAX_ACTIVE_DEVICES=2` در فایل نمونه به معنی دو دستگاه فعال هم‌زمان نیست.
- مجوز آفلاین در حالت عادی حداکثر ۷ روز است. حالت اضطراری پنل می‌تواند مجوز ۸ تا ۳۰ روزه صادر کند و انتقال دستگاه را متوقف می‌کند؛ منطق آن در `LauncherEmergencyOffline` است.
- دانلود از مسیر Prepare و پس از کنترل دسترسی انجام می‌شود. میزبان‌ها را با `LAUNCHER_ALLOWED_DOWNLOAD_HOSTS` مشخص کنید.
- نشست دانلود با `LAUNCHER_DOWNLOAD_SESSION_LEASE_MINUTES` کنترل می‌شود؛ متغیر قدیمی `LAUNCHER_DOWNLOAD_SESSION_TTL_MINUTES` مبنای فعلی نیست.
- انقضای URL روی خود میزبان دانلود به `LAUNCHER_SECURE_LINK_SECRET` و تنظیم متناظر Nginx وابسته است؛ امضای مسیر برنامه به‌تنهایی URL مقصد را منقضی نمی‌کند.
- بررسی پس‌زمینه فایل به‌طور پیش‌فرض HEAD و حجم را کنترل می‌کند. `LAUNCHER_DOWNLOAD_INTEGRITY_FULL_HASH=true` دریافت کامل و محاسبه hash سمت سرور را فعال می‌کند و برای فایل بزرگ هزینه ترافیک دارد.

برای جزئیات از [راهنمای لانچر](docs/launcher/README_FA.md) و [قرارداد endpointها](docs/launcher/API_ENDPOINTS_FA.md) شروع کنید.

<a id="testing"></a>

## تست و کنترل کیفیت

### PHP

```bash
composer run test
```

این اسکریپت config cache را پاک می‌کند و تست‌های Laravel را اجرا می‌کند. در `phpunit.xml`، دیتابیس SQLite حافظه‌ای، کش و session از نوع `array`، صف `sync` و mailer از نوع `array` هستند. تست را با محیط testing و بدون config cache محیط اصلی اجرا کنید.

اجرای هدفمند:

```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --filter=DownloadEntryTrackingTest
```

تست `DownloadClickCounterTest` به Redis واقعی آزمایشی نیاز دارد؛ پورت با `DOWNLOAD_COUNTER_TEST_REDIS_PORT` تعیین می‌شود. جزئیات در [راهنمای آمار دانلود](docs/download-box-tracking-fa.md) آمده است.

### JavaScript

تست‌ها از runner داخلی Node استفاده می‌کنند:

```bash
node --test tests/Frontend/download-box.test.mjs tests/Unit/Content/post-edit-lock.test.mjs tests/Unit/Seo/readability.test.mjs tests/Unit/localize-display-digits.test.mjs
```

نمونه بررسی مرورگری خوانایی در `tests/Browser/readability.html` است.

### Build و قالب کد

```bash
npm run build
php vendor/bin/pint --test
```

ورودی‌های چندگانه CSS و JavaScript در `vite.config.js` تعریف شده‌اند.

**وضعیت بررسی در ۲۰۲۶/۱۰/۱۶:** هر ۲۸ تست JavaScript موفق بودند. اجرای مستقیم PHPUnit برای مجموعه Unit با PHP 8.4 روی Windows، ۸۳ تست و ۳۲۹ assertion را اجرا کرد و ۳ خطا و ۱ مورد risky گزارش داد: دو خطای فایل موقت در `MediaPickerMultipleUploadTest` و یک خطای انتظار mock در `SyncNavigationMenuItemsActionTest`. همان تست منو risky نیز گزارش شد. مجموعه Feature و build کامل در این بررسی اجرا نشدند؛ نتیجه فوق به معنی موفقیت همه تست‌های پروژه نیست.

<a id="deployment"></a>

## استقرار و نگهداری

document root وب‌سرور باید **`public/`** باشد. `storage` و `bootstrap/cache` باید برای کاربر PHP قابل نوشتن باشند. HTTPS، `APP_ENV=production`، `APP_DEBUG=false`، `APP_URL` صحیح و سرویس‌ها را آماده کنید؛ برنامه در production تولید URL با HTTPS را اجباری می‌کند.

پس از پشتیبان‌گیری و کنترل تغییرات روی staging، مراحل اصلی سرور Linux:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
composer check-platform-reqs --no-dev
npm ci
npm run build
php artisan optimize:clear
php artisan migrate --force
php artisan icons:import
php artisan scribe:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan horizon:terminate
```

خروجی موفق هر مرحله را پیش از ادامه بررسی کنید. می‌توانید build فرانت‌اند را در CI انجام دهید و `public/build` را در artifact استقرار قرار دهید؛ در این صورت اجرای Node روی سرور نهایی لازم نیست.

در نصب تازه، seed و لینک storage را مطابق بخش نصب انجام دهید. در ارتقای نصب موجود، seeder موردنیاز را پس از بررسی اثر آن انتخاب کنید. تولید API docs باید با محیط و داده مناسب انجام شود.

پس از `horizon:terminate`، process manager باید Horizon را دوباره بالا بیاورد. برای worker عادی به‌جای آن `php artisan queue:restart` را اجرا کنید. cron و مصرف هر چهار صف را کنترل کنید.

فرمان ترکیبی `php artisan app:deploy` نیز وجود دارد: cacheها را پاک می‌کند، migration را با `--force` اجرا می‌کند، **تمام DatabaseSeeder** را دوباره اجرا می‌کند، آیکن و مستندات API را می‌سازد و Horizon را متوقف می‌کند. این فرمان build فرانت‌اند یا بازسازی cacheهای نهایی را انجام نمی‌دهد و اثر seederها باید پیش از استفاده روی داده موجود بررسی شود.

برای پایش، لاگ‌های `storage/logs`، وضعیت jobها، Scheduler، سوابق `backup_runs` و مسیرهای ورود، خرید و دانلود را بررسی کنید. آزمون بازیابی بک‌آپ را مطابق [راهنمای عملیاتی](docs/backup-operations-fa.md) انجام دهید.

<a id="wordpress"></a>

## مهاجرت وردپرس

connection جداگانه `wordpress` در `config/database.php` تعریف شده است:

```dotenv
WP_DB_HOST=127.0.0.1
WP_DB_PORT=3306
WP_DB_DATABASE=your_wordpress_database
WP_DB_USERNAME=your_wordpress_user
WP_DB_PASSWORD=your_wordpress_password
WP_DB_PREFIX=wp_
```

فرمان‌های `wp:*` برای بررسی داده و انتقال کاربر، نوشته، برگه، دسته و برچسب، دیدگاه، رسانه، تصویر شاخص، بازدید و متادیتای Yoast وجود دارند. نگاشت شناسه‌ها در ماژول `WordPressMigration` نگهداری می‌شود.

```bash
php artisan list wp
php artisan help wp:inspect
php artisan help wp:import-posts
```

این ابزارها جزو نصب تازه نیستند. ترتیب انتقال و وابستگی داده‌ها را روی نسخه آزمایشی بررسی کنید. `seo:import-yoast-redirects` و `seo:fix-meta-variables` نیز برای انتقال داده SEO موجودند.

<a id="troubleshooting"></a>

## رفع اشکال

| نشانه | بررسی و اقدام |
| --- | --- |
| `This cache store does not support tagging` | `CACHE_STORE=redis`، اتصال Redis و اجرای `php artisan config:clear` را کنترل کنید. |
| خطای `ext-pcntl` یا `ext-posix` | محیط نصب Horizon را به Linux یا WSL2 منتقل و `composer check-platform-reqs` را اجرا کنید. |
| خطای PHP با وجود `^8.2` در manifest | وابستگی‌های قفل‌شده PHP 8.3 می‌خواهند؛ نسخه CLI و PHP وب‌سرور را بررسی کنید. |
| دیتابیس ناموجود | نام واقعی و `DB_DATABASE` را تطبیق دهید؛ مقدار فایل نمونه `moddingwayy` است. |
| `Vite manifest not found` یا ظاهر ناقص | `npm ci` و `npm run build` را اجرا کنید؛ در توسعه فعال‌بودن Vite را کنترل کنید. |
| تصویر آپلودشده نمایش داده نمی‌شود | لینک `public/storage`، `APP_URL` و دسترسی storage را بررسی کنید. |
| پس از seed مدیر ندارید | Seeder کاربر نمی‌سازد؛ بخش ساخت اولین مدیر را اجرا کنید. |
| کار صفی در انتظار می‌ماند | connection و queue اختصاصی آن را کنترل کنید؛ صف پیش‌فرض به‌تنهایی کافی نیست. |
| آمار بازدید یا کلیک به‌روز نمی‌شود | Redis و Scheduler را بررسی کنید؛ انتقال به SQL دوره‌ای است. |
| ورود پیامکی خطا دارد | ارائه‌دهنده پیش‌فرض، اطلاعات سرویس و قالب OTP را در پنل تنظیم کنید. |
| `/docs/api` نمایش داده نمی‌شود | خروجی Scribe، ورود و مجوز `admin.api-docs` را بررسی کنید. |
| Scheduler روی Windows خطای `nice` یا `ionice` دارد | زمان‌بندی فعلی نقشه سایت برای Linux نوشته شده است؛ اجرای کامل را در Linux یا WSL انجام دهید. |

<a id="documentation"></a>

## مستندات تکمیلی

| موضوع | راهنما |
| --- | --- |
| توسعه | [معماری پروژه](docs/architecture.md) |
| شروع لانچر | [معرفی بک‌اند](docs/launcher/README_FA.md)، [شروع سریع](MW_LAUNCHER_READ_ME_FIRST_FA.md) |
| استقرار و قرارداد لانچر | [استقرار](docs/launcher/DEPLOYMENT_FA.md)، [API](docs/launcher/API_ENDPOINTS_FA.md) |
| دستگاه و آفلاین | [امنیت و مجوز آفلاین](docs/launcher/DEVICE_SECURITY_OFFLINE_FA.md)، [Reset و تعلیق](docs/launcher/DEVICE_RESET_AND_ACCOUNT_SUSPENSION_FA.md) |
| کلید محتوا و نصب‌کننده | [Content Key Pipe](docs/launcher/CONTENT_KEY_PIPE_FA.md)، [نصب‌کننده عمومی](docs/launcher/PUBLIC_INSTALLER_AND_LANDING_PERFORMANCE_FA.md) |
| نمونه داده | [Manifest پایه](docs/launcher/examples/base-manifest.json)، [مادها](docs/launcher/examples/mods.json) |
| فروش | [ترکیب کوپن](docs/coupon-stacking-fa.md)، [تحلیل فروش](docs/commerce-analytics-step-1.md) |
| آمار دانلود | [ثبت کلیک، انتقال و تست](docs/download-box-tracking-fa.md) |
| قرعه‌کشی | [عملیات](docs/raffle-operations-fa.md)، [تست دستی](docs/raffle-manual-test-fa.md) |
| اطلاع‌رسانی | [اعلان داخل سایت](docs/in-app-notifications-fa.md)، [پیامک مشتریان](docs/CUSTOMER_SMS_NOTIFICATIONS_FA.md)، [هشدار ورود](docs/LOGIN_ALERTS_FA.md) |
| مدیریت | [انتخاب‌گر آیکن](docs/icon-picker.md)، [تاریخچه تغییرات](docs/audit-history.md) |
| بک‌آپ | [راه‌اندازی و بازیابی](docs/backup-operations-fa.md) |

برخی راهنماها گزارش یک مقطع مشخص‌اند و نسخه یا وضعیت تست تاریخی دارند. برای رفتار فعلی، کد، config و lock fileها مرجع هستند؛ به‌ویژه صف بک‌آپ و نسخه Livewire را با تنظیمات فعلی تطبیق دهید.

<a id="license"></a>

## مجوز

مقدار `license` در `composer.json` برابر `MIT` است، اما فایل مستقل `LICENSE` برای کد اختصاصی در ریشه مخزن موجود نیست. وضعیت مجوز انتشار کد اختصاصی باید توسط مالک پروژه مشخص شود؛ مجوز هر وابستگی نیز مستقل است.
