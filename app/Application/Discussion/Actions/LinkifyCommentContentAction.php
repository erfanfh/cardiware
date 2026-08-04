<?php

namespace App\Application\Discussion\Actions;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class LinkifyCommentContentAction
{
    public function execute(?string $safeHtml): string
    {
        if (blank($safeHtml)) {
            return '';
        }

        /*
         * برای جلوگیری از پردازش غیرضروری متن‌هایی که هیچ لینکی ندارند.
         */
        if (
            !str_contains($safeHtml, 'http://') &&
            !str_contains($safeHtml, 'https://') &&
            !str_contains($safeHtml, 'www.')
        ) {
            return $safeHtml;
        }

        $document = new DOMDocument('1.0', 'UTF-8');

        $previousState = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="utf-8" ?>' .
            '<div data-comment-linkify-root="true">' .
            $safeHtml .
            '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        $xpath = new DOMXPath($document);

        /*
         * متن‌های داخل لینک، کد، اسکریپت و استایل را بررسی نمی‌کنیم.
         */
        $textNodes = $xpath->query(
            '//div[@data-comment-linkify-root="true"]//text()[
                not(ancestor::a)
                and not(ancestor::code)
                and not(ancestor::pre)
                and not(ancestor::script)
                and not(ancestor::style)
            ]'
        );

        if ($textNodes === false) {
            return $safeHtml;
        }

        /*
         * ابتدا Nodeها را داخل آرایه می‌ریزیم؛ چون هنگام پردازش،
         * ساختار DOM تغییر می‌کند.
         */
        $nodes = [];

        foreach ($textNodes as $textNode) {
            $nodes[] = $textNode;
        }

        foreach ($nodes as $textNode) {
            $this->replaceLinksInsideTextNode(
                document: $document,
                textNode: $textNode,
            );
        }

        $root = $xpath
            ->query('//div[@data-comment-linkify-root="true"]')
            ?->item(0);

        if (!$root instanceof DOMElement) {
            return $safeHtml;
        }

        return $this->innerHtml($root);
    }

    private function replaceLinksInsideTextNode(
        DOMDocument $document,
        DOMNode     $textNode
    ): void
    {
        $text = $textNode->nodeValue ?? '';

        if ($text === '') {
            return;
        }

        /*
         * پشتیبانی از:
         *
         * https://example.com
         * http://example.com
         * www.example.com
         */
        $pattern = '~(?:https?://|www\.)[^\s<]+~iu';

        if (
            preg_match_all(
                $pattern,
                $text,
                $matches,
                PREG_OFFSET_CAPTURE
            ) !== 1 &&
            empty($matches[0])
        ) {
            return;
        }

        $fragment = $document->createDocumentFragment();

        $currentOffset = 0;

        foreach ($matches[0] as [$rawUrl, $urlOffset]) {
            $beforeUrl = substr(
                $text,
                $currentOffset,
                $urlOffset - $currentOffset
            );

            if ($beforeUrl !== '') {
                $fragment->appendChild(
                    $document->createTextNode($beforeUrl)
                );
            }

            [$url, $trailingCharacters] = $this->separateTrailingCharacters(
                $rawUrl
            );

            $href = str_starts_with(
                mb_strtolower($url),
                'www.'
            )
                ? 'https://' . $url
                : $url;

            if ($this->isAllowedUrl($href)) {
                $anchor = $document->createElement('a');

                $anchor->setAttribute('href', $href);
                $anchor->setAttribute('target', '_blank');
                $anchor->setAttribute(
                    'rel',
                    'nofollow ugc noopener noreferrer'
                );

                $anchor->setAttribute(
                    'class',
                    implode(' ', [
                        'font-bold',
                        'text-brand-600',
                        'underline',
                        'decoration-brand-300',
                        'underline-offset-4',
                        'transition',
                        'hover:text-brand-700',
                        'dark:text-brand-300',
                        'dark:decoration-brand-500/50',
                        'dark:hover:text-brand-200',
                    ])
                );

                $anchor->appendChild(
                    $document->createTextNode($url)
                );

                $fragment->appendChild($anchor);
            } else {
                $fragment->appendChild(
                    $document->createTextNode($url)
                );
            }

            if ($trailingCharacters !== '') {
                $fragment->appendChild(
                    $document->createTextNode($trailingCharacters)
                );
            }

            $currentOffset = $urlOffset + strlen($rawUrl);
        }

        $remainingText = substr($text, $currentOffset);

        if ($remainingText !== '') {
            $fragment->appendChild(
                $document->createTextNode($remainingText)
            );
        }

        $textNode->parentNode?->replaceChild(
            $fragment,
            $textNode
        );
    }

    private function separateTrailingCharacters(string $value): array
    {
        /*
         * علائم انتهای جمله نباید جزئی از لینک باشند.
         *
         * مثلا:
         * https://example.com.
         * https://example.com،
         * https://example.com)
         */
        if (
            !preg_match(
                '/([.,،؛:!?؟)\]}»]+)$/u',
                $value,
                $matches
            )
        ) {
            return [$value, ''];
        }

        $trailingCharacters = $matches[1];

        return [
            mb_substr(
                $value,
                0,
                mb_strlen($value) - mb_strlen($trailingCharacters)
            ),
            $trailingCharacters,
        ];
    }

    private function isAllowedUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array(
            mb_strtolower((string)$scheme),
            ['http', 'https'],
            true
        );
    }

    private function innerHtml(DOMElement $element): string
    {
        $html = '';

        foreach ($element->childNodes as $childNode) {
            $html .= $element->ownerDocument?->saveHTML($childNode) ?? '';
        }

        return $html;
    }
}
