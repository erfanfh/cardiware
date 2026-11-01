<?php

namespace App\Application\Content\Actions;

use App\Support\Landings;

final class GetProductLandingPageDataAction
{
    public function execute(string $slug): array
    {
        $locale = Landings::localeFromRequest();
        $data = Landings::resolve(Landings::product($slug), $locale);

        abort_if($data === null, 404);

        // محصولِ غیرفعال از پنل، برای کاربران عادی به‌کلی مخفی است
        abort_if(($data['enabled'] ?? true) === false, 404);

        // بخش‌های مخفی‌شده از پنل رندر نمی‌شوند
        $data['sections'] = array_values(array_filter(
            $data['sections'] ?? [],
            fn ($section) => ($section['hidden'] ?? false) !== true,
        ));

        // صفحات جزئیاتِ غیرفعال از منو و دسترسی مستقیم حذف می‌شوند
        $data['pages'] = array_filter(
            $data['pages'] ?? [],
            fn ($page) => ($page['enabled'] ?? true) !== false,
        );

        return compact('slug', 'data', 'locale');
    }
}
