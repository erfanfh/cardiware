<?php

namespace App\Application\System\Actions\Footer;

use App\Domain\Content\Models\Post;

class GetFooterNewsAction
{
    /** @return array<int, array{title: string, url: string}> */
    public function execute(): array
    {
        $configured = get_setting('footer', 'news_items', []);

        if (is_array($configured) && count($configured) > 0) {
            return collect($configured)
                ->filter(fn (mixed $item): bool => is_array($item) && trim((string) ($item['title'] ?? '')) !== '')
                ->map(function (array $item): array {
                    $url = trim((string) ($item['url'] ?? ''));

                    return [
                        'title' => trim((string) $item['title']),
                        'url' => $this->safeUrl($url) ? $url : '',
                    ];
                })
                ->take(8)
                ->values()
                ->all();
        }

        return Post::query()
            ->whereNotNull('slug')
            ->latest('published_at')
            ->limit(4)
            ->get(['title', 'slug'])
            ->map(fn (Post $post): array => [
                'title' => $post->title,
                'url' => front_route('contents.detail', $post),
            ])
            ->all();
    }

    private function safeUrl(string $url): bool
    {
        return $url !== ''
            && preg_match('~^(?:https?://[^\s<>]+|/(?!/)[^\s<>]*)$~i', $url) === 1;
    }
}
