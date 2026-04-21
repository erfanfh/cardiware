<?php

namespace App\Application\Seo\Services;

final class PersianTextNormalizer
{
    public function normalize(string $text): string
    {
        $text = trim($text);

        $replacements = [
            'ي' => 'ی',
            'ك' => 'ک',
            'ة' => 'ه',
            'ۀ' => 'ه',
            'ؤ' => 'و',
            'أ' => 'ا',
            'إ' => 'ا',
            '‌' => ' ', // ZWNJ to space
            "\u{200C}" => ' ',
            "\u{200F}" => ' ',
            "\u{200E}" => ' ',
        ];

        $text = strtr($text, $replacements);

        $text = preg_replace('/[^\p{L}\p{N}\s\-]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return mb_strtolower(trim($text));
    }

    public function contains(string $haystack, string $needle): bool
    {
        $normalizedHaystack = $this->normalize($haystack);
        $normalizedNeedle = $this->normalize($needle);

        if ($normalizedNeedle === '') {
            return false;
        }

        return str_contains($normalizedHaystack, $normalizedNeedle);
    }

    public function wordCount(string $text): int
    {
        $normalized = $this->normalize($text);

        if ($normalized === '') {
            return 0;
        }

        $parts = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        return is_array($parts) ? count($parts) : 0;
    }

    /**
     * @return array<int, string>
     */
    public function words(string $text): array
    {
        $normalized = $this->normalize($text);

        if ($normalized === '') {
            return [];
        }

        $parts = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        return is_array($parts) ? array_values($parts) : [];
    }
}
