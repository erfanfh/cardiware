<?php

namespace App\Application\Seo\Actions\Sitemap;

use App\Application\Seo\Renderers\SitemapXmlRenderer;
use App\Application\Seo\Support\Sitemap\SitemapStorage;
use App\Application\Seo\Support\Sitemap\StaticSitemapGeneratorRegistry;
use Throwable;

class GenerateAllSitemapsAction
{
    public function __construct(
        private readonly StaticSitemapGeneratorRegistry $registry,
        private readonly SitemapStorage                 $storage,
        private readonly SitemapXmlRenderer             $renderer,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        $this->storage->startTemporaryGeneration();

        try {
            $indexItems = $this->registry
                ->enabledGenerators()
                ->flatMap(fn($generator) => $generator->generate())
                ->values()
                ->all();

            $this->storage->put(
                'sitemap_index.xml',
                $this->renderer->index($indexItems)
            );

            $this->storage->commitTemporaryGeneration();
        } catch (Throwable $exception) {
            $this->storage->discardTemporaryGeneration();

            throw $exception;
        }
    }
}
