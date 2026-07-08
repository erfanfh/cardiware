<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | فایل ترجمه پیام‌های اعتبارسنجی لاراول.
    | مسیر پیشنهادی:
    | lang/fa/validation.php
    |
    */

    'accepted' => 'فیلد :attribute باید پذیرفته شود.',
    'accepted_if' => 'وقتی :other برابر :value است، فیلد :attribute باید پذیرفته شود.',
    'active_url' => 'فیلد :attribute باید یک آدرس معتبر باشد.',
    'after' => 'فیلد :attribute باید تاریخی بعد از :date باشد.',
    'after_or_equal' => 'فیلد :attribute باید تاریخی برابر یا بعد از :date باشد.',
    'alpha' => 'فیلد :attribute فقط می‌تواند شامل حروف باشد.',
    'alpha_dash' => 'فیلد :attribute فقط می‌تواند شامل حروف، اعداد، خط تیره و زیرخط باشد.',
    'alpha_num' => 'فیلد :attribute فقط می‌تواند شامل حروف و اعداد باشد.',
    'array' => 'فیلد :attribute باید آرایه باشد.',
    'ascii' => 'فیلد :attribute فقط می‌تواند شامل حروف، اعداد و نمادهای تک‌بایتی باشد.',
    'before' => 'فیلد :attribute باید تاریخی قبل از :date باشد.',
    'before_or_equal' => 'فیلد :attribute باید تاریخی برابر یا قبل از :date باشد.',

    'between' => [
        'array' => 'فیلد :attribute باید بین :min تا :max آیتم داشته باشد.',
        'file' => 'حجم فایل :attribute باید بین :min تا :max کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید بین :min تا :max باشد.',
        'string' => 'فیلد :attribute باید بین :min تا :max کاراکتر باشد.',
    ],

    'boolean' => 'فیلد :attribute فقط می‌تواند درست یا نادرست باشد.',
    'can' => 'شما اجازه انجام این عملیات را برای :attribute ندارید.',
    'confirmed' => 'تکرار فیلد :attribute با مقدار اصلی مطابقت ندارد.',
    'contains' => 'فیلد :attribute باید شامل مقدار مورد نیاز باشد.',
    'current_password' => 'رمز عبور فعلی صحیح نیست.',
    'date' => 'فیلد :attribute باید یک تاریخ معتبر باشد.',
    'date_equals' => 'فیلد :attribute باید برابر با تاریخ :date باشد.',
    'date_format' => 'فرمت فیلد :attribute باید :format باشد.',
    'decimal' => 'فیلد :attribute باید :decimal رقم اعشار داشته باشد.',
    'declined' => 'فیلد :attribute باید رد شود.',
    'declined_if' => 'وقتی :other برابر :value است، فیلد :attribute باید رد شود.',
    'different' => 'فیلد :attribute و :other باید متفاوت باشند.',
    'digits' => 'فیلد :attribute باید دقیقاً :digits رقم باشد.',
    'digits_between' => 'فیلد :attribute باید بین :min تا :max رقم باشد.',
    'dimensions' => 'ابعاد تصویر :attribute معتبر نیست.',
    'distinct' => 'فیلد :attribute مقدار تکراری دارد.',
    'doesnt_end_with' => 'فیلد :attribute نباید با یکی از این موارد تمام شود: :values.',
    'doesnt_start_with' => 'فیلد :attribute نباید با یکی از این موارد شروع شود: :values.',
    'email' => 'فیلد :attribute باید یک ایمیل معتبر باشد.',
    'ends_with' => 'فیلد :attribute باید با یکی از این موارد تمام شود: :values.',
    'enum' => 'مقدار انتخاب‌شده برای :attribute معتبر نیست.',
    'exists' => 'مقدار انتخاب‌شده برای :attribute معتبر نیست.',
    'extensions' => 'پسوند فایل :attribute باید یکی از این موارد باشد: :values.',
    'file' => 'فیلد :attribute باید یک فایل معتبر باشد.',
    'filled' => 'فیلد :attribute باید مقدار داشته باشد.',
    'hex_color' => 'فیلد :attribute باید یک رنگ HEX معتبر باشد.',

    'gt' => [
        'array' => 'فیلد :attribute باید بیشتر از :value آیتم داشته باشد.',
        'file' => 'حجم فایل :attribute باید بیشتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید بیشتر از :value باشد.',
        'string' => 'فیلد :attribute باید بیشتر از :value کاراکتر باشد.',
    ],

    'gte' => [
        'array' => 'فیلد :attribute باید حداقل :value آیتم داشته باشد.',
        'file' => 'حجم فایل :attribute باید حداقل :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید حداقل :value باشد.',
        'string' => 'فیلد :attribute باید حداقل :value کاراکتر باشد.',
    ],

    'image' => 'فیلد :attribute باید تصویر باشد.',
    'in' => 'مقدار انتخاب‌شده برای :attribute معتبر نیست.',
    'in_array' => 'فیلد :attribute باید یکی از مقادیر موجود در :other باشد.',
    'integer' => 'فیلد :attribute باید عدد صحیح باشد.',
    'ip' => 'فیلد :attribute باید IP معتبر باشد.',
    'ipv4' => 'فیلد :attribute باید IPv4 معتبر باشد.',
    'ipv6' => 'فیلد :attribute باید IPv6 معتبر باشد.',
    'json' => 'فیلد :attribute باید JSON معتبر باشد.',
    'list' => 'فیلد :attribute باید یک لیست معتبر باشد.',
    'lowercase' => 'فیلد :attribute باید با حروف کوچک باشد.',

    'lt' => [
        'array' => 'فیلد :attribute باید کمتر از :value آیتم داشته باشد.',
        'file' => 'حجم فایل :attribute باید کمتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید کمتر از :value باشد.',
        'string' => 'فیلد :attribute باید کمتر از :value کاراکتر باشد.',
    ],

    'lte' => [
        'array' => 'فیلد :attribute نباید بیشتر از :value آیتم داشته باشد.',
        'file' => 'حجم فایل :attribute نباید بیشتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute نباید بیشتر از :value باشد.',
        'string' => 'فیلد :attribute نباید بیشتر از :value کاراکتر باشد.',
    ],

    'mac_address' => 'فیلد :attribute باید یک MAC Address معتبر باشد.',

    'max' => [
        'array' => 'فیلد :attribute نباید بیشتر از :max آیتم داشته باشد.',
        'file' => 'حجم فایل :attribute نباید بیشتر از :max کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute نباید بیشتر از :max باشد.',
        'string' => 'فیلد :attribute نباید بیشتر از :max کاراکتر باشد.',
    ],

    'max_digits' => 'فیلد :attribute نباید بیشتر از :max رقم باشد.',
    'mimes' => 'فایل :attribute باید یکی از فرمت‌های زیر باشد: :values.',
    'mimetypes' => 'فایل :attribute باید یکی از نوع‌های زیر باشد: :values.',

    'min' => [
        'array' => 'فیلد :attribute باید حداقل :min آیتم داشته باشد.',
        'file' => 'حجم فایل :attribute باید حداقل :min کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید حداقل :min باشد.',
        'string' => 'فیلد :attribute باید حداقل :min کاراکتر باشد.',
    ],

    'min_digits' => 'فیلد :attribute باید حداقل :min رقم باشد.',
    'missing' => 'فیلد :attribute نباید ارسال شود.',
    'missing_if' => 'وقتی :other برابر :value است، فیلد :attribute نباید ارسال شود.',
    'missing_unless' => 'مگر اینکه :other برابر :value باشد، فیلد :attribute نباید ارسال شود.',
    'missing_with' => 'وقتی :values ارسال شده است، فیلد :attribute نباید ارسال شود.',
    'missing_with_all' => 'وقتی :values ارسال شده‌اند، فیلد :attribute نباید ارسال شود.',
    'multiple_of' => 'فیلد :attribute باید مضربی از :value باشد.',
    'not_in' => 'مقدار انتخاب‌شده برای :attribute معتبر نیست.',
    'not_regex' => 'فرمت فیلد :attribute معتبر نیست.',
    'numeric' => 'فیلد :attribute باید عدد باشد.',

    'password' => [
        'letters' => 'رمز عبور باید حداقل شامل یک حرف باشد.',
        'mixed' => 'رمز عبور باید حداقل شامل یک حرف بزرگ و یک حرف کوچک باشد.',
        'numbers' => 'رمز عبور باید حداقل شامل یک عدد باشد.',
        'symbols' => 'رمز عبور باید حداقل شامل یک نماد باشد.',
        'uncompromised' => 'این رمز عبور در نشت‌های اطلاعاتی مشاهده شده است. لطفاً رمز عبور دیگری انتخاب کنید.',
    ],

    'present' => 'فیلد :attribute باید وجود داشته باشد.',
    'present_if' => 'وقتی :other برابر :value است، فیلد :attribute باید وجود داشته باشد.',
    'present_unless' => 'مگر اینکه :other برابر :value باشد، فیلد :attribute باید وجود داشته باشد.',
    'present_with' => 'وقتی :values وجود دارد، فیلد :attribute باید وجود داشته باشد.',
    'present_with_all' => 'وقتی :values وجود دارند، فیلد :attribute باید وجود داشته باشد.',

    'prohibited' => 'فیلد :attribute مجاز نیست.',
    'prohibited_if' => 'وقتی :other برابر :value است، فیلد :attribute مجاز نیست.',
    'prohibited_if_accepted' => 'وقتی :other پذیرفته شده باشد، فیلد :attribute مجاز نیست.',
    'prohibited_if_declined' => 'وقتی :other رد شده باشد، فیلد :attribute مجاز نیست.',
    'prohibited_unless' => 'مگر اینکه :other یکی از مقادیر :values باشد، فیلد :attribute مجاز نیست.',
    'prohibits' => 'وقتی فیلد :attribute ارسال شود، ارسال فیلد :other مجاز نیست.',

    'regex' => 'فرمت فیلد :attribute معتبر نیست.',
    'required' => 'فیلد :attribute الزامی است.',
    'required_array_keys' => 'فیلد :attribute باید شامل این کلیدها باشد: :values.',
    'required_if' => 'وقتی :other برابر :value است، فیلد :attribute الزامی است.',
    'required_if_accepted' => 'وقتی :other پذیرفته شده باشد، فیلد :attribute الزامی است.',
    'required_if_declined' => 'وقتی :other رد شده باشد، فیلد :attribute الزامی است.',
    'required_unless' => 'مگر اینکه :other برابر یکی از مقادیر :values باشد، فیلد :attribute الزامی است.',
    'required_with' => 'وقتی :values وجود دارد، فیلد :attribute الزامی است.',
    'required_with_all' => 'وقتی :values وجود دارند، فیلد :attribute الزامی است.',
    'required_without' => 'وقتی :values وجود ندارد، فیلد :attribute الزامی است.',
    'required_without_all' => 'وقتی هیچ‌کدام از :values وجود ندارند، فیلد :attribute الزامی است.',

    'same' => 'فیلد :attribute باید با :other برابر باشد.',

    'size' => [
        'array' => 'فیلد :attribute باید دقیقاً :size آیتم داشته باشد.',
        'file' => 'حجم فایل :attribute باید دقیقاً :size کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید دقیقاً :size باشد.',
        'string' => 'فیلد :attribute باید دقیقاً :size کاراکتر باشد.',
    ],

    'starts_with' => 'فیلد :attribute باید با یکی از این موارد شروع شود: :values.',
    'string' => 'فیلد :attribute باید متن باشد.',
    'timezone' => 'فیلد :attribute باید منطقه زمانی معتبر باشد.',
    'ulid' => 'فیلد :attribute باید ULID معتبر باشد.',
    'unique' => 'این :attribute قبلاً ثبت شده است.',
    'uploaded' => 'آپلود فایل :attribute با خطا مواجه شد.',
    'uppercase' => 'فیلد :attribute باید با حروف بزرگ باشد.',
    'url' => 'فیلد :attribute باید URL معتبر باشد.',
    'uuid' => 'فیلد :attribute باید UUID معتبر باشد.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | نمونه:
    | 'custom' => [
    |     'email' => [
    |         'required' => 'لطفاً ایمیل را وارد کنید.',
    |     ],
    | ],
    |
    */

    'custom' => [
        'password' => [
            'confirmed' => 'تکرار رمز عبور با رمز عبور مطابقت ندارد.',
        ],
        'mobile' => [
            'regex' => 'شماره موبایل باید با فرمت معتبر وارد شود.',
        ],
        'phone' => [
            'regex' => 'شماره تلفن باید با فرمت معتبر وارد شود.',
        ],
        'slug' => [
            'regex' => 'نامک فقط می‌تواند شامل حروف انگلیسی، عدد و خط تیره باشد.',
        ],
        'code' => [
            'regex' => 'فرمت کد واردشده معتبر نیست.',
        ],
        'sku' => [
            'regex' => 'فرمت SKU معتبر نیست.',
        ],
        'national_code' => [
            'regex' => 'کد ملی باید با فرمت معتبر وارد شود.',
        ],
        'postal_code' => [
            'regex' => 'کد پستی باید با فرمت معتبر وارد شود.',
        ],
        'sheba' => [
            'regex' => 'شماره شبا باید با فرمت معتبر وارد شود.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | این بخش باعث می‌شود به جای نام فیلدهای انگلیسی، نام فارسی آن‌ها
    | در خطاها نمایش داده شود.
    |
    */

    'attributes' => [

        // عمومی
        'id' => 'شناسه',
        'ids' => 'شناسه‌ها',
        'title' => 'عنوان',
        'name' => 'نام',
        'first_name' => 'نام',
        'last_name' => 'نام خانوادگی',
        'full_name' => 'نام و نام خانوادگی',
        'display_name' => 'نام نمایشی',
        'username' => 'نام کاربری',
        'email' => 'ایمیل',
        'mobile' => 'موبایل',
        'phone' => 'تلفن',
        'password' => 'رمز عبور',
        'password_confirmation' => 'تکرار رمز عبور',
        'current_password' => 'رمز عبور فعلی',
        'new_password' => 'رمز عبور جدید',
        'new_password_confirmation' => 'تکرار رمز عبور جدید',
        'description' => 'توضیحات',
        'summary' => 'خلاصه',
        'content' => 'محتوا',
        'body' => 'متن',
        'text' => 'متن',
        'message' => 'پیام',
        'note' => 'یادداشت',
        'notes' => 'یادداشت‌ها',
        'status' => 'وضعیت',
        'type' => 'نوع',
        'sort' => 'مرتب‌سازی',
        'direction' => 'جهت مرتب‌سازی',
        'sort_order' => 'ترتیب نمایش',
        'is_active' => 'فعال بودن',
        'is_visible' => 'قابل نمایش بودن',
        'is_required' => 'الزامی بودن',
        'created_at' => 'زمان ایجاد',
        'updated_at' => 'زمان به‌روزرسانی',
        'deleted_at' => 'زمان حذف',
        'published_at' => 'زمان انتشار',
        'starts_at' => 'زمان شروع',
        'expires_at' => 'زمان پایان',
        'start_date' => 'تاریخ شروع',
        'end_date' => 'تاریخ پایان',
        'start_time' => 'زمان شروع',
        'end_time' => 'زمان پایان',
        'date' => 'تاریخ',
        'time' => 'زمان',
        'from' => 'از',
        'to' => 'تا',
        'file' => 'فایل',
        'files' => 'فایل‌ها',
        'image' => 'تصویر',
        'picture' => 'تصویر',
        'avatar' => 'آواتار',
        'cover' => 'کاور',
        'icon' => 'آیکون',
        'url' => 'آدرس',
        'link' => 'لینک',
        'slug' => 'نامک',
        'code' => 'کد',
        'token' => 'توکن',
        'metadata' => 'داده‌های جانبی',

        // کاربر و نقش
        'user_id' => 'کاربر',
        'user_ids' => 'کاربران',
        'role_id' => 'نقش',
        'role_ids' => 'نقش‌ها',
        'permission_id' => 'دسترسی',
        'permission_ids' => 'دسترسی‌ها',
        'national_code' => 'کد ملی',
        'birth_date' => 'تاریخ تولد',
        'gender' => 'جنسیت',

        // محصول / کاتالوگ
        'product_id' => 'محصول',
        'product_ids' => 'محصولات',
        'category_id' => 'دسته‌بندی',
        'category_ids' => 'دسته‌بندی‌ها',
        'tag_id' => 'برچسب',
        'tag_ids' => 'برچسب‌ها',
        'brand_id' => 'برند',
        'sku' => 'SKU',
        'price' => 'قیمت',
        'base_price' => 'قیمت پایه',
        'sale_price' => 'قیمت فروش',
        'regular_price' => 'قیمت اصلی',
        'final_price' => 'قیمت نهایی',
        'min_price' => 'حداقل قیمت',
        'max_price' => 'حداکثر قیمت',
        'quantity' => 'تعداد',
        'stock' => 'موجودی',
        'stock_quantity' => 'تعداد موجودی',
        'max_quantity_per_order' => 'حداکثر تعداد در هر سفارش',
        'download_limit' => 'محدودیت دانلود',
        'download_expires_in_days' => 'مدت اعتبار دانلود',
        'structure' => 'ساختار محصول',
        'attribute_id' => 'ویژگی',
        'attribute_ids' => 'ویژگی‌ها',
        'attribute_value_id' => 'مقدار ویژگی',
        'attribute_value_ids' => 'مقادیر ویژگی',
        'sale_item_id' => 'آیتم فروش',
        'sale_item_ids' => 'آیتم‌های فروش',
        'addon_id' => 'خدمت افزوده',
        'addon_ids' => 'خدمات افزوده',

        // فروشگاه / مارکت‌پلیس
        'store_id' => 'فروشگاه',
        'store_ids' => 'فروشگاه‌ها',
        'seller_id' => 'فروشنده',
        'commission_rate' => 'نرخ کمیسیون',
        'settlement_status' => 'وضعیت تسویه',

        // سفارش / سبد خرید
        'cart_id' => 'سبد خرید',
        'cart_item_id' => 'آیتم سبد خرید',
        'order_id' => 'سفارش',
        'order_ids' => 'سفارش‌ها',
        'order_number' => 'شماره سفارش',
        'order_item_id' => 'آیتم سفارش',
        'payment_method' => 'روش پرداخت',
        'subtotal_amount' => 'مبلغ اولیه',
        'discount_amount' => 'مبلغ تخفیف',
        'coupon_discount_amount' => 'مبلغ تخفیف کدتخفیف',
        'total_amount' => 'مبلغ نهایی',
        'final_total_amount' => 'مبلغ نهایی آیتم',
        'unit_price' => 'قیمت واحد',

        // تخفیف / کدتخفیف
        'coupon_id' => 'کدتخفیف',
        'coupon_code' => 'کد تخفیف',
        'coupon_eligible_amount' => 'مبلغ مشمول کدتخفیف',
        'coupon_applied_at' => 'زمان اعمال کدتخفیف',
        'coupon_reservation_id' => 'رزرو کدتخفیف',
        'value' => 'مقدار',
        'max_discount_amount' => 'سقف تخفیف',
        'scope' => 'محدوده اعمال',
        'applies_to_all_users' => 'اعمال برای همه کاربران',
        'applies_to_addons' => 'اعمال روی خدمات افزوده',
        'minimum_order_amount' => 'حداقل مبلغ سفارش',
        'usage_limit' => 'محدودیت کل استفاده',
        'usage_limit_per_user' => 'محدودیت استفاده هر کاربر',
        'used_count' => 'تعداد مصرف‌شده',
        'target_type' => 'نوع هدف',
        'target_id' => 'هدف',
        'bulk_action' => 'عملیات گروهی',

        // پرداخت
        'payment_id' => 'پرداخت',
        'gateway' => 'درگاه پرداخت',
        'gateway_status' => 'وضعیت درگاه',
        'gateway_message' => 'پیام درگاه',
        'tracking_code' => 'کد رهگیری',
        'reference_id' => 'شناسه مرجع',
        'transaction_id' => 'شناسه تراکنش',
        'paid_at' => 'زمان پرداخت',
        'failed_at' => 'زمان خطای پرداخت',
        'amount' => 'مبلغ',

        // مالی / کیف پول
        'wallet_id' => 'کیف پول',
        'balance' => 'موجودی',
        'credit' => 'اعتبار',
        'debit' => 'بدهی',
        'iban' => 'شماره شبا',
        'sheba' => 'شماره شبا',
        'card_number' => 'شماره کارت',
        'account_number' => 'شماره حساب',

        // محتوا / سئو
        'seo_title' => 'عنوان سئو',
        'seo_description' => 'توضیحات سئو',
        'meta_title' => 'عنوان متا',
        'meta_description' => 'توضیحات متا',
        'canonical_url' => 'آدرس canonical',
        'og_title' => 'عنوان OG',
        'og_description' => 'توضیحات OG',
        'og_type' => 'نوع OG',
        'keywords' => 'کلمات کلیدی',
        'excerpt' => 'خلاصه مطلب',
        'reading_time' => 'زمان مطالعه',

        // دیدگاه
        'comment_id' => 'دیدگاه',
        'rating' => 'امتیاز',
        'rating_percent' => 'درصد امتیاز',
        'parent_id' => 'والد',

        // آدرس / ارسال
        'address' => 'آدرس',
        'province_id' => 'استان',
        'city_id' => 'شهر',
        'district_id' => 'منطقه',
        'postal_code' => 'کد پستی',
        'latitude' => 'عرض جغرافیایی',
        'longitude' => 'طول جغرافیایی',
        'shipping_method' => 'روش ارسال',
        'shipping_amount' => 'هزینه ارسال',
        'receiver_name' => 'نام گیرنده',
        'receiver_mobile' => 'موبایل گیرنده',

        // تنظیمات
        'key' => 'کلید',
        'setting_key' => 'کلید تنظیمات',
        'setting_value' => 'مقدار تنظیمات',
        'group' => 'گروه',
        'config' => 'تنظیمات',
        'options' => 'گزینه‌ها',

        // بازه‌های فیلتر
        'created_at_from' => 'تاریخ ایجاد از',
        'created_at_to' => 'تاریخ ایجاد تا',
        'updated_at_from' => 'تاریخ به‌روزرسانی از',
        'updated_at_to' => 'تاریخ به‌روزرسانی تا',
        'starts_at_from' => 'زمان شروع از',
        'starts_at_to' => 'زمان شروع تا',
        'expires_at_from' => 'زمان پایان از',
        'expires_at_to' => 'زمان پایان تا',
        'price_min' => 'حداقل قیمت',
        'price_max' => 'حداکثر قیمت',
        'amount_min' => 'حداقل مبلغ',
        'amount_max' => 'حداکثر مبلغ',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Values
    |--------------------------------------------------------------------------
    |
    | برای نمایش فارسی مقدارهای شرطی مثل required_if.
    |
    */

    'values' => [
        'status' => [
            'active' => 'فعال',
            'inactive' => 'غیرفعال',
            'pending' => 'در انتظار بررسی',
            'approved' => 'تأیید شده',
            'rejected' => 'رد شده',
            'draft' => 'پیش‌نویس',
            'published' => 'منتشر شده',
            'archived' => 'آرشیو شده',
            'deleted' => 'حذف شده',
        ],

        'type' => [
            'digital' => 'دیجیتال',
            'physical' => 'فیزیکی',
            'simple' => 'ساده',
            'variable' => 'متغیر',
            'percentage' => 'درصدی',
            'fixed_amount' => 'مبلغ ثابت',
        ],

        'scope' => [
            'order' => 'کل سفارش',
            'products' => 'محصولات',
            'store' => 'فروشگاه',
        ],

        'payment_method' => [
            'gateway' => 'درگاه پرداخت',
            'wallet' => 'کیف پول',
        ],

        'direction' => [
            'asc' => 'صعودی',
            'desc' => 'نزولی',
        ],

        'is_active' => [
            '0' => 'غیرفعال',
            '1' => 'فعال',
        ],

        'applies_to_all_users' => [
            '0' => 'کاربران خاص',
            '1' => 'همه کاربران',
        ],

        'applies_to_addons' => [
            '0' => 'خیر',
            '1' => 'بله',
        ],
    ],

];
