<?php

namespace App\Infrastructure\Support;

class ReadingTimeEstimator
{
    public static function estimate(?string $content): int
    {
        $text = strip_tags((string)$content);
        $text = preg_replace('/\s+/u', ' ', trim($text));

        $wordCount = str_word_count($text);

        return max(1, (int)ceil($wordCount / 200));
    }
}
