<?php

namespace App\Domain\Content\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * صفحه‌ی لندینگ ساخته‌شده در پنل (لندینگ‌ساز).
 *
 * theme: preset، primary، accent، mode (dark|light)، pattern، bg_image{id,path}
 * blocks: فهرست بلوک‌ها؛ هر بلوک type دارد و فیلدهای مشترک (title، subtitle، body،
 *         image، دکمه‌ها، items_text، product_ids، video_url، countdown_at، coupon_code، variant)
 * seo: meta_title، meta_description، og_image{id,path}، noindex
 */
class LandingPage extends Model
{
    public const THEME_PRESETS = [
        'pgl' => ['label' => 'نارنجی PGL (تیره)', 'primary' => '#f97316', 'accent' => '#facc15', 'mode' => 'dark'],
        'fifa' => ['label' => 'آبی FIFA (تیره)', 'primary' => '#2563eb', 'accent' => '#22d3ee', 'mode' => 'dark'],
        'vip' => ['label' => 'طلایی VIP (تیره)', 'primary' => '#d97706', 'accent' => '#fde68a', 'mode' => 'dark'],
        'festival' => ['label' => 'قرمز جشنواره (روشن)', 'primary' => '#e11d48', 'accent' => '#f59e0b', 'mode' => 'light'],
        'emerald' => ['label' => 'سبز (روشن)', 'primary' => '#059669', 'accent' => '#84cc16', 'mode' => 'light'],
        'violet' => ['label' => 'بنفش (تیره)', 'primary' => '#7c3aed', 'accent' => '#f0abfc', 'mode' => 'dark'],
        'custom' => ['label' => 'دلخواه (رنگ‌های زیر)', 'primary' => '#f97316', 'accent' => '#facc15', 'mode' => 'dark'],
    ];

    public const BLOCK_TYPES = [
        'hero' => 'هدر اصلی (عنوان، توضیح، تصویر، دکمه)',
        'features' => 'ویژگی‌ها (کارت‌های آیکن‌دار)',
        'products' => 'نمایش محصولات',
        'image_text' => 'تصویر و متن',
        'video' => 'ویدیو (آپارات / یوتیوب)',
        'countdown' => 'شمارش معکوس',
        'coupon' => 'کد تخفیف با دکمه کپی',
        'faq' => 'پرسش‌های متداول',
        'gallery' => 'گالری تصاویر',
        'cta' => 'دعوت به اقدام (بنر دکمه‌دار)',
        'text' => 'متن آزاد',
    ];

    protected $fillable = [
        'title', 'slug', 'status', 'theme', 'blocks', 'seo', 'starts_at', 'ends_at', 'created_by_user_id',
    ];

    protected $casts = [
        'theme' => 'array',
        'blocks' => 'array',
        'seo' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'views_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === 'published'
            && ($this->starts_at === null || $this->starts_at->lte(now()))
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function url(): string
    {
        return route('landing-pages.show', ['landingSlug' => $this->slug]);
    }

    /** @return array{preset: string, primary: string, accent: string, mode: string, pattern: string, bg_image: ?string} */
    public function resolvedTheme(): array
    {
        $theme = (array) ($this->theme ?? []);
        $preset = array_key_exists($theme['preset'] ?? '', self::THEME_PRESETS) ? $theme['preset'] : 'pgl';
        $base = self::THEME_PRESETS[$preset];
        $custom = $preset === 'custom';

        return [
            'preset' => $preset,
            'primary' => $custom && self::isColor($theme['primary'] ?? null) ? $theme['primary'] : $base['primary'],
            'accent' => $custom && self::isColor($theme['accent'] ?? null) ? $theme['accent'] : $base['accent'],
            'mode' => in_array($theme['mode'] ?? null, ['dark', 'light'], true) && $custom ? $theme['mode'] : $base['mode'],
            'pattern' => in_array($theme['pattern'] ?? null, ['dots', 'grid', 'diag'], true) ? $theme['pattern'] : 'none',
            'bg_image' => self::safeUrl(data_get($theme, 'bg_image.path')),
        ];
    }

    public static function isColor(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1;
    }

    /** فقط لینک‌های http(s)، مسیرهای داخلی سایت و لنگر صفحه؛ بقیه (javascript: و ...) حذف می‌شوند. */
    public static function safeUrl(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        // «/\\host» را مرورگرها مثل «//host» (آدرس خارجی) می‌خوانند؛ فقط مسیر داخلی واقعی مجاز است.
        if (str_starts_with($value, '#') || (str_starts_with($value, '/') && ! str_starts_with($value, '//') && ! str_starts_with($value, '/\\'))) {
            return $value;
        }

        return preg_match('#^https?://#i', $value) === 1 ? $value : null;
    }

    /** خطوط «بخش | بخش | ...» برای ویژگی‌ها، پرسش‌ها و گالری. @return list<list<string>> */
    public static function lines(mixed $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $text))
            ->map(fn (string $line) => array_map('trim', explode('|', $line)))
            ->filter(fn (array $parts) => ($parts[0] ?? '') !== '')
            ->values()
            ->all();
    }

    public static function videoEmbedUrl(mixed $url): ?string
    {
        $url = trim((string) $url);
        if (preg_match('#aparat\.com/v/([A-Za-z0-9_-]+)#', $url, $m)) {
            return 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame';
        }
        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        return null;
    }
}
