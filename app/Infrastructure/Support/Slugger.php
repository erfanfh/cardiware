<?php

namespace App\Infrastructure\Support;

final class Slugger
{
    public static function slug(string $string, string $separator = '-'): string
    {
        $s = trim($string);

        // Normalize whitespace + ZWNJ (half-space)
        $s = str_replace(["\u{200C}", "\u{200D}", "\u{FEFF}"], ' ', $s); // ZWNJ, ZWJ, BOM
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        // Normalize digits (Persian/Arabic -> English)
        $s = strtr($s, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        // Keep only letters/numbers (fa/en) and spaces; drop punctuation/symbols
        $s = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $s) ?? $s;

        // Spaces -> separator
        $sep = preg_quote($separator, '/');
        $s = preg_replace('/\s+/u', $separator, trim($s)) ?? $s;

        // Collapse multiple separators
        $s = preg_replace('/' . $sep . '{2,}/', $separator, $s) ?? $s;

        // Trim separators from ends
        $s = preg_replace('/^' . $sep . '+|' . $sep . '+$/', '', $s) ?? $s;

        return $s;
    }
}
