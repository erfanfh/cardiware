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
            '‌' => ' ',
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
        $normalizedHaystack = $this->normalize($this->toPlainText($haystack));
        $normalizedNeedle = $this->normalize($this->toPlainText($needle));

        if ($normalizedNeedle === '') {
            return false;
        }

        return str_contains($normalizedHaystack, $normalizedNeedle);
    }

    public function wordCount(string $text): int
    {
        $normalized = $this->normalize($this->toPlainText($text));

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
        $normalized = $this->normalize($this->toPlainText($text));

        if ($normalized === '') {
            return [];
        }

        $parts = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        return is_array($parts) ? array_values($parts) : [];
    }

    public function toPlainText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $text = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $text) ?? $text;
        $text = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $text) ?? $text;
        $text = preg_replace('/\[[^\]]+\]/u', ' ', $text) ?? $text;

        $text = strip_tags($text);

        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
