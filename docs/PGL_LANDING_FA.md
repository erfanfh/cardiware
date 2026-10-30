# قالب‌های لندینگ PGL

فایل‌های بسته `pgl-landing-package-v1.0.zip` در مسیرهای متناظر پروژه قرار گرفته‌اند. تمام ۳۵۲ فایل فرانت بسته (Blade، CSS، JavaScript و تصاویر) ابتدا بدون تغییر بایت منتقل شدند. سپس به درخواست کاربر، فقط علامت شروع کامنت معیوب در `master.blade.php` اصلاح شد تا متن فنی بالای صفحه نمایش داده نشود. دو فایل جدید در `resources/views/templates` فقط قالب اصلی بسته را به سیستم قالب برگه‌های پروژه معرفی می‌کنند.

## استفاده در مدیریت

در ایجاد یا ویرایش برگه، از فهرست قالب یکی از این گزینه‌ها را انتخاب و برگه را منتشر کنید:

- «لندینگ PGL Ultimate 27» (`pgl-ultimate`)
- «لندینگ PGL FIFA 23» (`pgl-fifa-23`)

قالب‌ها HTML مستقل بسته را نمایش می‌دهند. متن، قیمت، لینک‌ها و ترتیب بخش‌ها از تنظیمات اصلی کارفرما خوانده می‌شوند؛ محتوای ویرایشگر برگه جایگزین این تنظیمات نمی‌شود. لینک‌های داخلی بسته به مسیرهای اصلی محصولات اشاره دارند، حتی اگر برای برگه اسلاگ دیگری انتخاب کنید.

## مسیرها

- `/pgl-ultimate` و `/pgl-fifa-23`: لندینگ‌های فارسی
- `/en/pgl-ultimate` و `/en/pgl-fifa-23`: نسخه‌های انگلیسی
- زیرمسیرهای `leagues`، `stadiums`، `trophies` و `faces`: جزئیات
- نمونه صفحه لیگ: `/pgl-ultimate/leagues/persian-gulf-pro-league`
- نمونه صفحه تیم: `/pgl-ultimate/leagues/persian-gulf-pro-league/sepahan-fc`

مسیرهای اصلی لندینگ مستقیماً از config رندر می‌شوند و ساخت برگه لازم ندارند. برای همان اسلاگ‌ها، روت لندینگ بر روت CMS اولویت دارد. قالب برگه برای استفاده در اسلاگ‌های دیگر و پیش‌نمایش مدیریت نیز در دسترس است.

## محل فایل‌ها

- قالب‌های قابل انتخاب: `resources/views/templates/pgl-*.blade.php`
- ویوها و partialهای اصلی: `resources/views/front/landings`
- CSS و JavaScript اصلی: `resources/css/front/landing.css` و `resources/js/front/{landing,kit-viewer}.js`
- تصاویر اصلی: `public/images/landings`
- محتوای محصولات: `config/product-landings.php`
- کاتالوگ لیگ‌ها و تیم‌ها: `config/landing-catalog.php`
- آماده‌سازی داده قالب: `app/Application/Content/Actions/GetProductLandingPageDataAction.php`
- کنترلرها: `app/Http/Controllers/Front/Landings`
- ابزار مورد استفاده ویوهای اصلی: `app/Support/Landings.php`
- روت‌ها: `routes/front/landings.php`؛ قبل از catch-all محتوای سایت ثبت شده‌اند و فقط کلیدهای محصولات تعریف‌شده را می‌گیرند.

## نصب روی محیط دیگر

```bash
npm ci
npm run build
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

دو وابستگی `alpinejs` و `three` در manifest و lock ثبت شده‌اند. فایل‌های CSS و JS لندینگ ورودی مستقل Vite دارند. فایل‌های تولیدشده در `public/build` مطابق روال پروژه در Git ثبت نمی‌شوند.

## نکات خود بسته

لینک‌های خرید و لانچر، اطلاعات نمایشی و تصاویر نمونه عیناً مانند بسته باقی مانده‌اند. تنها اصلاح فرانت، تصحیح علامت کامنت Blade داخل `master.blade.php` برای حذف نمایش ناخواسته متن فنی است. اتصال خرید واقعی به مقدارهای config نیاز دارد.

## بررسی

تست `tests/Feature/Content/ProductLandingTemplateTest.php` رندر هر دو محصول در هر دو زبان، صفحات جزئیات و لیگ و تیم، ثبت قالب‌ها، نمایش قالب از مسیر CMS، خطای ۴۰۴ و حفظ مسیرهای عمومی موجود را بررسی می‌کند.

```bash
php artisan test tests/Feature/Content/ProductLandingTemplateTest.php
```
