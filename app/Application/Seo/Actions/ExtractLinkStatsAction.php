<?php

namespace App\Application\Seo\Actions;

final class ExtractLinkStatsAction
{
    /**
     * @return array{
     *     internal_link_count: int,
     *     outbound_link_count: int,
     *     outbound_follow_link_count: int,
     *     internal_link_anchor_texts: array<int, string>
     * }
     */
    public function execute(?string $html, string $siteUrl): array
    {
        if (blank($html)) {
            return [
                'internal_link_count' => 0,
                'outbound_link_count' => 0,
                'outbound_follow_link_count' => 0,
                'internal_link_anchor_texts' => [],
            ];
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

        $anchors = $dom->getElementsByTagName('a');

        $internalLinkCount = 0;
        $outboundLinkCount = 0;
        $outboundFollowLinkCount = 0;
        $internalLinkAnchorTexts = [];

        $siteHost = parse_url($siteUrl, PHP_URL_HOST);
        $normalizedSiteHost = $this->normalizeHost($siteHost);

        foreach ($anchors as $anchor) {
            $href = trim((string)$anchor->getAttribute('href'));

            if ($href === '' || $this->shouldIgnoreHref($href)) {
                continue;
            }

            $anchorText = trim((string)$anchor->textContent);

            if ($this->isInternalUrl($href, $normalizedSiteHost)) {
                $internalLinkCount++;

                if ($anchorText !== '') {
                    $internalLinkAnchorTexts[] = $anchorText;
                }

                continue;
            }

            $outboundLinkCount++;

            if ($this->isFollowLink($anchor->getAttribute('rel'))) {
                $outboundFollowLinkCount++;
            }
        }

        return [
            'internal_link_count' => $internalLinkCount,
            'outbound_link_count' => $outboundLinkCount,
            'outbound_follow_link_count' => $outboundFollowLinkCount,
            'internal_link_anchor_texts' => $internalLinkAnchorTexts,
        ];
    }

    protected function shouldIgnoreHref(string $href): bool
    {
        return str_starts_with($href, '#')
            || str_starts_with($href, 'mailto:')
            || str_starts_with($href, 'tel:')
            || str_starts_with($href, 'javascript:');
    }

    protected function isInternalUrl(string $url, ?string $siteHost): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if ($host === null) {
            return true;
        }

        return $siteHost !== null
            && $this->normalizeHost($host) === $siteHost;
    }

    protected function isFollowLink(string $rel): bool
    {
        $tokens = preg_split('/\s+/u', mb_strtolower(trim($rel)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return !in_array('nofollow', $tokens, true);
    }

    protected function normalizeHost(?string $host): ?string
    {
        if ($host === null || $host === '') {
            return null;
        }

        $host = mb_strtolower(trim($host));

        return str_starts_with($host, 'www.')
            ? substr($host, 4)
            : $host;
    }
}
