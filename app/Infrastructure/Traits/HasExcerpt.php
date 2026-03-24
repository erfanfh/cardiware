<?php

namespace App\Infrastructure\Traits;

use Illuminate\Support\Str;

trait HasExcerpt
{
    /**
     * Return a short summary from excerpt or body.
     *
     * @param int $words
     * @param string $end
     * @return string
     */
    public function excerpt(int $words = 30, string $end = '...'): string
    {
        $excerpt = $this->getRawExcerptSource();

        if ($excerpt === '') {
            return '';
        }

        return Str::words($excerpt, $words, $end);
    }

    /**
     * Return the full cleaned source text before truncation.
     *
     * @return string
     */
    protected function getRawExcerptSource(): string
    {
        $text = $this->excerpt ?: $this->body ?: '';

        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }
}
