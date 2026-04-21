<?php

namespace App\Application\Seo\Services;

use DOMDocument;
use DOMXPath;
use Throwable;

final class HtmlContentAnalyzer
{
    /**
     * @return array{
     *     plain_text: string,
     *     h1_count: int,
     *     h1_texts: array<int, string>,
     *     h2_texts: array<int, string>,
     *     h3_texts: array<int, string>,
     *     first_paragraph: string
     * }
     */
    public function analyze(string $html): array
    {
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');

        if (trim($html) === '') {
            return [
                'plain_text' => $plainText,
                'h1_count' => 0,
                'h1_texts' => [],
                'h2_texts' => [],
                'h3_texts' => [],
                'first_paragraph' => '',
            ];
        }

        try {
            $document = new DOMDocument();
            libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            libxml_clear_errors();

            $xpath = new DOMXPath($document);

            $h1Texts = $this->extractTexts($xpath, '//h1');
            $h2Texts = $this->extractTexts($xpath, '//h2');
            $h3Texts = $this->extractTexts($xpath, '//h3');
            $paragraphs = $this->extractTexts($xpath, '//p');

            return [
                'plain_text' => $plainText,
                'h1_count' => count($h1Texts),
                'h1_texts' => $h1Texts,
                'h2_texts' => $h2Texts,
                'h3_texts' => $h3Texts,
                'first_paragraph' => $paragraphs[0] ?? '',
            ];
        } catch (Throwable) {
            return [
                'plain_text' => $plainText,
                'h1_count' => 0,
                'h1_texts' => [],
                'h2_texts' => [],
                'h3_texts' => [],
                'first_paragraph' => '',
            ];
        }
    }

    /**
     * @return array<int, string>
     */
    protected function extractTexts(DOMXPath $xpath, string $expression): array
    {
        $nodes = $xpath->query($expression);

        if ($nodes === false) {
            return [];
        }

        $items = [];

        foreach ($nodes as $node) {
            $text = trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '');

            if ($text !== '') {
                $items[] = $text;
            }
        }

        return $items;
    }
}
