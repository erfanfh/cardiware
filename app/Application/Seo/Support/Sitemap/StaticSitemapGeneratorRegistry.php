<?php

namespace App\Application\Seo\Support\Sitemap;

use App\Application\Seo\Contracts\StaticSitemapGeneratorInterface;
use Illuminate\Support\Collection;

class StaticSitemapGeneratorRegistry
{
    /**
     * @param iterable<int, StaticSitemapGeneratorInterface> $generators
     */
    public function __construct(
        private readonly iterable $generators,
    )
    {
    }

    /**
     * @return Collection<int, StaticSitemapGeneratorInterface>
     */
    public function enabledGenerators(): Collection
    {
        return collect($this->generators)
            ->filter(fn(StaticSitemapGeneratorInterface $generator) => $generator->isEnabled())
            ->values();
    }
}
