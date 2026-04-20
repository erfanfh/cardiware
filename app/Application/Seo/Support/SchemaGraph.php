<?php

namespace App\Application\Seo\Support;

final class SchemaGraph
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $nodes = [];

    /**
     * @param array<string, mixed> $node
     */
    public function add(array $node): self
    {
        if ($node !== []) {
            $this->nodes[] = $node;
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => $this->nodes,
        ];
    }
}
