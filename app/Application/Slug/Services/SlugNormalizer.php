<?php

namespace App\Application\Slug\Services;

use Illuminate\Support\Str;

class SlugNormalizer
{
    /**
     * Normalize a raw slug-like string into a clean value.
     *
     * This method keeps Persian characters intact and only performs
     * structural cleanup such as trimming, replacing spaces with dashes,
     * removing slashes, collapsing duplicated dashes, and lowercasing
     * Latin characters.
     *
     * @param string $value
     * @return string
     */
    public function normalize(string $value): string
    {
        $slug = trim($value);
        $slug = str_replace(['/', '\\'], '-', $slug);
        $slug = preg_replace('/[\s_]+/u', '-', $slug) ?? $slug;
        $slug = preg_replace('/-+/u', '-', $slug) ?? $slug;
        $slug = trim($slug, '-');

        return Str::lower($slug);
    }
}
