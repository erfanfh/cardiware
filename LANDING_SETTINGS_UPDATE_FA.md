# به‌روزرسانی تنظیمات لندینگ + ردیابی خطای 500 — ۱۴۰۵/۰۸/۰۹

## الف) قابلیت جدید: فعال/غیرفعال‌سازی و ویرایش کامل (پیاده‌سازی شد)

**۱. کلیدهای جدید در فرم ادمین** (settings → product-landings):
- هر محصول (ultimate / fifa23): چک‌باکس **«فعال باشد»** — اگر خاموش شود، کل لندینگ و
  همه صفحات آن برای کاربران عادی **۴۰۴** می‌شود.
- هر صفحه جزئیات (leagues / stadiums / trophies / faces): چک‌باکس **«فعال باشد»** —
  صفحه از منو حذف و دسترسی مستقیم به آن ۴۰۴ می‌شود.
- هر بخش (section) لندینگ: چک‌باکس **«مخفی باشد»** — بخش در صفحه رندر نمی‌شود ولی
  در پنل می‌ماند و بعداً قابل برگرداندن است.
- افزودن/حذف/جابه‌جایی بخش‌ها از قبل با دکمه‌های «افزودن / حذف مورد / بالاتر / پایین‌تر»
  هر فهرست موجود بود؛ اکنون ساختار همه بخش‌ها (متن دوزبانه، آمار، گالری، قیمت‌گذاری،
  سوالات و...) کاملاً قابل ویرایش است.

**۲. تغییرات فایل‌ها:**
| فایل | تغییر |
|---|---|
| `config/product-landings.php` | کلید `enabled` در هر محصول و هر صفحه + `hidden` در هر ۲۲ سکشن (نسخه قبل: `product-landings.php.bak-flags`) |
| `app/Application/Content/Actions/GetProductLandingPageDataAction.php` | ۴۰۴ محصول غیرفعال + حذف سکشن‌های hidden و صفحات غیرفعال از دیتای ویو |
| `app/Http/Controllers/Front/Landings/LandingPageController.php` | هر سه اکشن (show/league/team) از همان Action دیتا می‌گیرند تا غیرفعال‌سازی همه‌جا برقرار باشد |
| `app/Infrastructure/.../ProductLandingsSettings.php` | `document()` سندِ ذخیره‌شده را روی پیش‌فرض config سوار می‌کند — ردیف‌های قدیمیِ فاقد کلیدهای جدید، خودکار تکمیل می‌شوند (فهرست‌ها همان منطق قبلی: حذف‌شده‌ها بازنمی‌گردند) |
| `app/Application/Content/Support/ProductLandingContentSchema.php` | برچسب فارسی enabled/hidden + فیلدِ غایب در payload دیگر خطا نیست ( سازگاری با ردیف‌های قدیمی) |
| `app/Application/System/Actions/UpdateSettingAction.php` | خطای واقعیِ ذخیره با جزئیات لاگ می‌شود (قبلاً `Log::info` بی‌جزئیات) |

**۳. تست‌شده به‌صورت لوکال (sqlite، کل مسیر ذخیره/خواندن):**
ذخیره ساختار جدید ✅، ذخیره سند قدیمی فاقد کلیدهای جدید ✅، ۴۰۴ محصول غیرفعال ✅،
حذف صفحه غیرفعال از منو ✅ (stadiums غایب بود)، حذف سکشن hidden از رندر ✅،
فرم ادمین با ۱۶ چک‌باکس رندر شد ✅.

## ب) خطای 500 موقع ذخیره (settings/product-landings/catalog)

کد را کامل در محیط لوکال بازسازی کردم (نصب وابستگی‌ها + sqlite + اجرای عین مسیر
کنترلر): **ذخیره با محتوای پیش‌فرض config بدون هیچ خطایی انجام می‌شود**؛ رندر فرم و
شِمای هر چهار سند (ultimate/fifa23/pages/catalog) هم سالم است. صفحات عمومی لندینگ
روی سایت هم همه ۲۰۰ هستند (یعنی مسیر خواندن تنظیمات سالم است) و درخواست PUT بدون
CSRF هم درست ۴۱۹ می‌گیرد (یعنی بدنه‌ی چند ده کیلوبایتی به لاراول می‌رسد و WAF مانع نیست).

نتیجه: خطا مخصوص **محیط سرور** است، نه کد. پس از دیپلوی این نسخه، یک بار دیگر
ذخیره کنید و سپس به‌ترتیب:

1. **لاگ واقعی حالا ثبت می‌شود**: `storage/logs/laravel.log` را باز کنید و آخرین
   `Setting persist failed` را ببینید — فیلد `exception` و `message` دقیقاً علت را می‌گوید.
2. اگر استثنا `QueryException` با «Data too long» یا «Invalid JSON text» بود:
   نوع ستون را چک کنید — باید `json` باشد:
   `SHOW COLUMNS FROM settings LIKE 'value';`
   (مایگریشن `2026_03_08_..._create_settings_table` باید اجرا شده باشد؛
   اگر جدول قدیمی است: `ALTER TABLE settings MODIFY value JSON NOT NULL;`)
3. روی سرور اجرا کنید: `php artisan migrate` و سپس `php artisan optimize:clear`
   (اگر config کش شده، فایل‌های config جدید دیده نمی‌شوند).
4. اگر لاگ `BadMethodCallException ... does not support tagging` داشت: درایور کش
   سرور باید Redis باشد (`CACHE_STORE=redis`) چون `AuthServiceProvider::Gate::before`
   از `Cache::tags` استفاده می‌کند.
5. تست تشخیصی سریع: آیا زبانه‌های ultimate یا fifa23 هم موقع ذخیره ۵۰۰ می‌دهند؟
   - فقط catalog → مشکل اندازه/نوع ستون (بند ۲)
   - همه → مشکل کش/دیتابیس (بند ۳ و ۴)

## ج) نکته‌ی دیپلوی

- بعد از آپلود، حتماً `php artisan optimize:clear` (ساختار فیلدهای فرم از config
  ساخته می‌شود و کش قدیمی فرمِ بدون چک‌باکس‌ها را نشان می‌دهد).
- اولین ذخیره روی هر زبانه ممکن است چند ثانیه طول بکشد (اعتبارسنجی بازگشتی کل سند).

## د) یافته‌های تکمیلی عیب‌یابی امشب

۱. **فایل `err.html`** (ذخیره‌شده امشب ۰۰:۳۸) صفحه‌ی خطای **لوکالِ V8** است:
`ViewException: syntax error, unexpected token "="` — یعنی روی V8 یکی از ویوها
بعد از اعمال پچ، خراب کامپایل شده. با اسکن تمام ۸۲۸ فایل blade در **V9**، همه‌ی
ویوهای واقعی سالم کامپایل شدند (۳ فایل خراب فقط پسوند `.rej` دارند که Blade
لودشان نمی‌کند).

۲. **۴ hunk پچ loyalty روی V9 ریجکت شده** (هم‌زمان ۰۰:۳۸) — این فایل‌ها:
- `app/Application/Loyalty/Actions/GetAudienceClubOverviewAction.php.rej`
- `resources/views/front/account/pages/loyalty.blade.php.rej` (۸۵KB — بخش عمده)
- `resources/views/front/account/partials/dashboard-loyalty-card.blade.php.rej`
- `resources/views/front/account/partials/hero.blade.php.rej`

پچ می‌خواست نمایش «سطح باشگاه مخاطبان» را از `loyalty_tier` قدیمی به
`loyalty_level` جدید (با badge و رنگ از `colorClasses()`) ارتقا دهد؛ چون نسخه‌ی
V9 این ویوها با پایه‌ی پچ فرق داشت، hunkها اعمال نشدند. نتیجه: صفحه‌ی باشگاه
مخاطبان کاربر، **نشان سطح جدید را ندارد** (خرابی تولید نمی‌کند، ولی ویژگی ناقص است).
توصیه: یا این ۴ hunk را دستی روی V9 اعمال کنید، یا فایل `.patch` اصلی را بفرستید
تا انطباق‌یافته با V9 بازساخته شود.

۳. **درس دیپلوی:** اگر همین پچ با روش patch روی سرور لایو هم اعمال شده، ممکن است
hunkهای دیگری آن‌جا ریجکت/نیمه‌اعمال شده باشد — بعد از هر دیپلویِ پچ‌محور،
`.rej` ها را روی سرور هم چک کنید (`find . -name "*.rej"`) و بعد `php artisan optimize:clear`.
اگر روی لایو hunk مربوط به product-landings نیمه‌اعمال مانده باشد، همان می‌تواند
منشأ همان ۵۰۰ باشد؛ در لاگ جدید (بند ب-۱) دقیقاً معلوم می‌شود.
