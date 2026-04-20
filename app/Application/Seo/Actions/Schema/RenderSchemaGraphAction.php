<?php

namespace App\Application\Seo\Actions\Schema;

final class RenderSchemaGraphAction
{
    /**
     * @param array<string, mixed> $graph
     */
    public function execute(array $graph): string
    {
        return json_encode(
            $graph,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        ) ?: '{}';
    }
}
