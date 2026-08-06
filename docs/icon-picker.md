# Icon library and admin icon picker

این زیرساخت آیکون‌های SVG را از فایل‌های JSON وارد دیتابیس می‌کند و در تمام فرم‌های ادمین یک picker مشترک ارائه می‌دهد.

## ساختار فایل JSON

هر فایل باید یک آرایه JSON باشد و بهتر است فقط یک مجموعه آیکون را در خود داشته باشد:

```json
[
  {
    "name": "hugeicons:home-01",
    "svg": "<svg ...>...</svg>"
  }
]
```

هر آیکون پس از ایمپورت یک شناسه دیتابیسی و یک عنوان کامل مانند `hugeicons:home-01` دارد. خود SVG در جدول `icons` نگهداری و پیش از ذخیره پاک‌سازی می‌شود.

## محل فایل‌های JSON

فایل‌های JSON را داخل مسیر زیر قرار دهید:

```text
resources/icon-sets
```

این مسیر بخشی از سورس پروژه است، در Git نگهداری می‌شود و چون داخل `public` قرار ندارد، کاربران نمی‌توانند فایل‌ها را مستقیما دانلود کنند.

نمونه ساختار:

```text
resources/
└── icon-sets/
    ├── hugeicons.json
    ├── solar.json
    └── tabler.json
```

## ایمپورت و همگام‌سازی

برای همگام‌سازی تمام فایل‌ها اجرا کنید:

```bash
php artisan icons:import
```

ایمپورت یک فایل مشخص با مسیر کامل پروژه:

```bash
php artisan icons:import resources/icon-sets/hugeicons.json
```

یا فقط با نام فایل:

```bash
php artisan icons:import hugeicons.json
```

تعیین عنوان نمایشی مجموعه:

```bash
php artisan icons:import hugeicons.json --label="Huge Icons"
```

اجرای مجدد حتی اگر checksum فایل تغییر نکرده باشد:

```bash
php artisan icons:import --force
```

ایمپورت به‌صورت همگام‌سازی انجام می‌شود. پس از کامل شدن موفق ایمپورت، آیکون‌هایی که دیگر داخل فایل نیستند از همان مجموعه حذف می‌شوند. فایل‌های بدون تغییر براساس checksum رد می‌شوند.

کامند `icons:import` داخل `app:deploy` نیز اجرا می‌شود؛ بنابراین بعد از هر دیپلوی، فایل‌های جدید و تغییرکرده به‌صورت خودکار با دیتابیس همگام می‌شوند.

## استفاده در یک فیلد عادی

```blade
<x-admin.field
    label="آیکون"
    for="icon"
    :error="$errors->first('icon_id')"
>
    <x-admin.form.icon-picker
        name="icon"
        :id-value="old('icon_id', $model->icon_id ?? null)"
        :value="old('icon', $model->icon ?? null)"
    />
</x-admin.field>
```

خروجی پیش‌فرض فرم شامل هر دو مقدار است:

```text
icon_id = 125
icon = hugeicons:home-01
```

نام ورودی شناسه به‌صورت خودکار از نام اصلی ساخته می‌شود. برای تعیین نام سفارشی از `id-name` استفاده کنید:

```blade
<x-admin.form.icon-picker
    name="icon_title"
    id-name="icon_id"
    :id-value="$model->icon_id"
    :value="$model->icon_title"
/>
```

`value` همچنین می‌تواند یک آرایه باشد:

```blade
<x-admin.form.icon-picker
    name="icon"
    :value="[
        'id' => $model->icon_id,
        'title' => $model->icon,
    ]"
/>
```

## استفاده داخل repeater

آیتم اولیه repeater باید هر دو فیلد را داشته باشد:

```blade
<x-admin.form.repeater
    name="items"
    :items="$items"
    :default-item="[
        'title' => '',
        'icon_id' => null,
        'icon' => null,
    ]"
>
    <x-admin.form.icon-picker
        mode="repeater"
        name="icon"
        compact
    />
</x-admin.form.repeater>
```

خروجی فرم:

```text
items[0][icon_id] = 125
items[0][icon] = hugeicons:home-01
```

اگر نام فیلد شناسه متفاوت باشد:

```blade
<x-admin.form.icon-picker
    mode="repeater"
    name="icon_title"
    id-name="selected_icon_id"
/>
```

هر picker داخل repeater با `itemKey` همان آیتم ایزوله می‌شود و انتخاب یک آیکون روی آیتم‌های دیگر اثری ندارد.

## اعتبارسنجی پیشنهادی

```php
[
    'icon_id' => ['nullable', 'integer', 'exists:icons,id'],
    'icon' => ['nullable', 'string', 'max:255'],
]
```

برای repeater:

```php
[
    'items.*.icon_id' => ['nullable', 'integer', 'exists:icons,id'],
    'items.*.icon' => ['nullable', 'string', 'max:255'],
]
```

بهتر است در Action ذخیره‌سازی، عنوان را براساس `icon_id` از جدول `icons` بخوانید تا ورودی کاربر منبع نهایی حقیقت نباشد.

## نمایش آیکون ذخیره‌شده

```blade
<x-icon
    :name="$model->icon"
    class="h-5 w-5 text-slate-700"
/>
```

رنگ آیکون‌هایی که از `currentColor` استفاده می‌کنند از کلاس رنگ والد گرفته می‌شود.

## نکات معماری

- JSONهای بزرگ به‌صورت stream خوانده می‌شوند و کامل داخل حافظه قرار نمی‌گیرند.
- نتایج picker صفحه‌بندی می‌شوند و کل کتابخانه وارد HTML صفحه نمی‌شود.
- پیش‌نمایش آیکون‌های repeater با درخواست batch بارگذاری می‌شود.
- lookup علاوه بر SVG، شناسه و عنوان آیکون را نیز برمی‌گرداند.
- SVG قبل از ذخیره از elementها و attributeهای خطرناک پاک‌سازی می‌شود.
- checksum فایل مانع ایمپورت تکراری فایل‌های بدون تغییر می‌شود.
