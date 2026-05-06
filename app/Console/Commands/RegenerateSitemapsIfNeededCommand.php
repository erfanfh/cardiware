<?php

namespace App\Console\Commands;

use App\Application\Seo\Actions\Sitemap\GenerateAllSitemapsAction;
use App\Application\Seo\Actions\Sitemap\MarkSitemapsDirtyAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RegenerateSitemapsIfNeededCommand extends Command
{
    protected $signature = 'sitemap:regenerate-if-needed';

    protected $description = 'Regenerate static sitemaps only when they are marked as dirty.';

    public function handle(GenerateAllSitemapsAction $action): int
    {
        if (!Cache::pull(MarkSitemapsDirtyAction::CACHE_KEY)) {
            $this->info('Sitemaps are clean.');

            return self::SUCCESS;
        }

        $this->info('Sitemaps are dirty. Regenerating...');

        $action->execute();

        $this->info('Sitemaps regenerated successfully.');

        return self::SUCCESS;
    }
}
