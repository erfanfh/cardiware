<?php

namespace App\Application\Content\Support;

use Illuminate\Validation\ValidationException;

/** Typed content form schema, derived exclusively from the shipped defaults. */
final class ProductLandingContentSchema
{
    public function infer(array $samples, string $key = ''): array
    {
        $present = array_values(array_filter($samples, static fn ($value) => $value !== null));
        $first = $present[0] ?? '';
        $schema = ['label' => $this->label($key), 'nullable' => count($present) !== count($samples), 'default' => $first];

        $types = [];
        foreach ($present as $sample) {
            $types[$this->valueType($sample)][] = $sample;
        }
        if (count($types) > 1) {
            $schema['kind'] = 'union';
            foreach ($types as $type => $examples) {
                $schema['variants'][$type] = $this->infer($examples, $key);
            }
            return $schema;
        }

        if (is_array($first)) {
            if (array_is_list($first)) {
                $items = array_merge(...$present);
                $schema['kind'] = 'list';
                $schema['variants'] = [];
                foreach ($items as $item) {
                    $variant = is_array($item) && isset($item['type']) ? $item['type'] : '_';
                    $schema['variants'][$variant][] = $item;
                }
                foreach ($schema['variants'] as $variant => $examples) {
                    $schema['variants'][$variant] = $this->infer($examples, $variant === '_' ? 'item' : $variant);
                }
            } else {
                $schema['kind'] = 'object';
                $schema['fields'] = [];
                $keys = array_unique(array_merge(...array_map('array_keys', $present)));
                foreach ($keys as $field) {
                    $values = [];
                    foreach ($present as $example) {
                        if (array_key_exists($field, $example)) {
                            $values[] = $example[$field];
                        }
                    }
                    $schema['fields'][$field] = $this->infer($values, (string) $field);
                    $schema['fields'][$field]['optional'] = count($values) !== count($present)
                        || in_array($field, ['enabled', 'hidden', 'pgl_team_id'], true);
                }
            }
        } else {
            $schema['kind'] = is_bool($first) ? 'boolean' : (is_numeric($first) && !is_string($first) ? 'number' : 'string');
            $schema['min'] = match ($key) { 'lat' => -90, 'lng' => -180, 'frames' => 1, default => 0 };
            $schema['max'] = match ($key) { 'lat' => 90, 'lng' => 180, 'frames' => 360, default => 1000000000 };
            $schema['multiline'] = in_array($key, ['description', 'desc', 'text', 'intro', 'subtitle', 'answer', 'a', 'about', 'fa', 'en'], true);
            $schema['key'] = $key;
            $schema['media'] = in_array($key, ['src', 'image', 'hero_image', 'poster', 'texture', 'logo'], true);
            if (in_array($key, ['type', 'theme'], true)) {
                $schema['choices'] = array_values(array_unique($present));
            }
        }

        return $schema;
    }

    public function validate(mixed $value, array $schema, string $path = 'content'): void
    {
        $fail = static function (string $message) use ($path, $schema): never {
            throw ValidationException::withMessages([$path => $schema['label'].': '.$message]);
        };

        if ($value === null && $schema['nullable']) {
            return;
        }

        if ($schema['kind'] === 'union') {
            $type = $this->valueType($value);
            if (!isset($schema['variants'][$type])) {
                $fail('نوع محتوا معتبر نیست.');
            }
            $this->validate($value, $schema['variants'][$type], $path);
            return;
        }

        if ($schema['kind'] === 'object') {
            if (!is_array($value) || array_diff(array_keys($value), array_keys($schema['fields']))) {
                $fail('ساختار فیلدها معتبر نیست.');
            }
            foreach ($schema['fields'] as $key => $child) {
                if (!array_key_exists($key, $value)) {
                    // فیلد غایب مجاز است: سندهای ذخیره‌شده‌ی قدیمی فیلدهای تازه‌ی
                    // config را ندارند و هنگام خواندن با پیش‌فرض تکمیل می‌شوند.
                    continue;
                }
                $this->validate($value[$key], $child, $path.'.'.$key);
            }
            return;
        }

        if ($schema['kind'] === 'list') {
            if (!is_array($value) || !array_is_list($value) || count($value) > 200) {
                $fail('فهرست باید حداکثر ۲۰۰ مورد داشته باشد.');
            }
            foreach ($value as $index => $item) {
                $variant = is_array($item) && isset($item['type']) ? $item['type'] : '_';
                if (!isset($schema['variants'][$variant])) {
                    $fail('نوع مورد انتخاب‌شده معتبر نیست.');
                }
                $this->validate($item, $schema['variants'][$variant], $path.'.'.$index);
            }
            return;
        }

        if ($schema['kind'] === 'number') {
            if ((!is_int($value) && !is_float($value)) || !is_finite((float) $value) || $value < $schema['min'] || $value > $schema['max']) {
                $fail('عدد واردشده خارج از محدوده مجاز است.');
            }
            return;
        }
        if ($schema['kind'] === 'boolean') {
            if (!is_bool($value)) {
                $fail('مقدار انتخاب‌شده معتبر نیست.');
            }
            return;
        }
        if (!is_string($value) || mb_strlen($value) > 20000) {
            $fail('متن باید حداکثر ۲۰ هزار نویسه داشته باشد.');
        }
        if (isset($schema['choices']) && !in_array($value, $schema['choices'], true)) {
            $fail('نوع قالب قابل تغییر نیست.');
        }
        if (in_array($schema['key'], ['accent', 'secondary', 'color'], true) && !preg_match('/^#(?:[a-f0-9]{3}|[a-f0-9]{4}|[a-f0-9]{6}|[a-f0-9]{8})$/i', $value)) {
            $fail('رنگ را به‌صورت کد هگز وارد کنید.');
        }
        $asset = in_array($schema['key'], ['src', 'image', 'hero_image', 'poster', 'texture', 'base', 'logo'], true);
        $relativeAsset = $asset && preg_match('~^[a-z0-9_-]+(?:[./][a-z0-9_%.\/-]+)+$~i', $value);
        if (in_array($schema['key'], ['href', 'url', 'buy_url', 'launcher_url', 'product_url', 'src', 'image', 'hero_image', 'poster', 'texture', 'base', 'logo', 'video_url'], true)
            && $value !== '' && !$relativeAsset && (!preg_match('~^(?:https?://|/(?!/)|\#|mailto:|tel:)[^\s<>"\x00-\x1f]*$~i', $value))) {
            $fail('لینک یا مسیر تصویر معتبر وارد کنید.');
        }
    }

    public function label(string $key): string
    {
        return [
            'fa' => 'فارسی', 'en' => 'English', 'item' => 'مورد',
            'product_name' => 'نام محصول', 'brand' => 'نام برند', 'theme' => 'قالب رنگی', 'game' => 'نام بازی',
            'seo' => 'اطلاعات سئو', 'title' => 'عنوان', 'description' => 'توضیحات', 'buy_url' => 'لینک خرید',
            'launcher_url' => 'لینک لانچر', 'product_url' => 'لینک محصول', 'nav' => 'منوی بالای صفحه',
            'pages' => 'صفحات جزئیات', 'sections' => 'بخش‌های لندینگ', 'footer' => 'پاورقی',
            'type' => 'نوع بخش', 'hero' => 'بخش ابتدایی', 'badge' => 'برچسب', 'highlight' => 'بخش رنگی عنوان',
            'subtitle' => 'زیرعنوان', 'primary' => 'دکمه اصلی', 'secondary' => 'دکمه دوم / رنگ دوم',
            'label' => 'متن نمایشی', 'href' => 'لینک', 'url' => 'آدرس', 'image' => 'تصویر', 'src' => 'آدرس تصویر',
            'video_url' => 'لینک ویدئو', 'video' => 'ویدئو', 'stats' => 'آمار', 'value' => 'مقدار',
            'marquee' => 'نوار متحرک', 'items' => 'موارد', 'logo' => 'لوگو', 'slug' => 'شناسه مسیر', 'tag' => 'کد کوتاه',
            'features' => 'ویژگی‌ها', 'icon' => 'آیکن', 'stat' => 'آمار ویژگی', 'desc' => 'توضیحات', 'page' => 'صفحه مرتبط',
            'leagues' => 'لیگ‌ها', 'eyebrow' => 'برچسب بالای بخش', 'iran' => 'ایران', 'globe' => 'جهان',
            'teams' => 'تیم‌ها', 'level' => 'سطح', 'gallery' => 'گالری', 'images' => 'تصاویر', 'caption' => 'توضیح تصویر',
            'steps' => 'مراحل نصب', 'pricing' => 'قیمت‌گذاری', 'plans' => 'طرح‌های خرید', 'name' => 'نام',
            'price' => 'قیمت', 'old_price' => 'قیمت قبلی', 'period' => 'واحد / دوره قیمت', 'cta' => 'دعوت به اقدام',
            'note' => 'یادداشت', 'changelog' => 'تغییرات نسخه‌ها', 'version' => 'نسخه', 'date' => 'تاریخ',
            'notes' => 'تغییرات', 'faq' => 'سوالات متداول', 'q' => 'سوال', 'a' => 'پاسخ',
            'about' => 'درباره برند', 'links' => 'لینک‌ها', 'copyright' => 'متن کپی‌رایت',
            'hero_image' => 'تصویر ابتدایی', 'intro' => 'متن معرفی', 'meta' => 'آمار صفحه',
            'stadiums' => 'استادیوم‌ها', 'trophies' => 'جام‌ها', 'faces' => 'چهره‌ها', 'country' => 'کشور',
            'teams_count' => 'تعداد تیم‌ها', 'detail' => 'جزئیات تیم', 'tags' => 'برچسب‌ها',
            'kit3d' => 'کیت سه‌بعدی', 'texture' => 'تصویر کیت', 'accent' => 'رنگ اصلی',
            'blocks' => 'بخش‌های محتوا', 'bullets' => 'فهرست ویژگی‌ها', 'text' => 'متن', 'poster' => 'پوستر',
            'spinner' => 'چرخش ۳۶۰ درجه', 'base' => 'پوشه فریم‌ها', 'frames' => 'تعداد فریم‌ها',
            'tilt-cards' => 'کارت‌های متحرک', 'capacity' => 'ظرفیت', 'club' => 'باشگاه', 'city' => 'شهر',
            'lat' => 'عرض جغرافیایی', 'lng' => 'طول جغرافیایی', 'cards' => 'کارت‌ها', 'body' => 'متن',
            'enabled' => 'فعال باشد (برای کاربران عادی نمایش داده شود)',
            'hidden' => 'مخفی باشد (در لندینگ رندر نشود)',
            'pgl_team_id' => 'شناسه تیم در دیتابیس بازیکنان پچ (برای نمایش ترکیب)',
        ][$key] ?? str_replace(['-', '_'], ' ', $key);
    }

    private function valueType(mixed $value): string
    {
        return is_array($value) ? (array_is_list($value) ? 'list' : 'object')
            : (is_bool($value) ? 'boolean' : (is_int($value) || is_float($value) ? 'number' : 'string'));
    }
}
