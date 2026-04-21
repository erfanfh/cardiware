<?php

namespace App\Application\Seo\Actions;

final class ExtractFirstParagraphAction
{
    public function execute(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();

        // جلوگیری از خراب شدن UTF-8
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

        $paragraphs = $dom->getElementsByTagName('p');

        foreach ($paragraphs as $p) {
            $text = trim($p->textContent ?? '');

            if ($this->isValidParagraph($text)) {
                return $text;
            }
        }

        return null;
    }

    protected function isValidParagraph(string $text): bool
    {
        // حداقل طول (می‌تونی tweak کنی)
        if (mb_strlen($text) < 30) {
            return false;
        }

        // فقط فاصله یا چیزهای بی‌معنی نباشه
        if (preg_match('/^[\s\p{P}]+$/u', $text)) {
            return false;
        }

        return true;
    }
}
