<?php

namespace App\Application\Seo\Actions\Redirect;

use Illuminate\Support\Str;

class NormalizeRedirectPathAction
{
    public function execute(?string $path): string
    {
        $path = trim((string)$path);

        if ($path === '') {
            return '/';
        }

        $path = parse_url($path, PHP_URL_PATH) ?: $path;

        $path = '/' . ltrim($path, '/');

        $path = preg_replace('#/+#', '/', $path) ?: $path;

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return Str::lower($path);
    }
}
