<?php

namespace App\Console\Commands;

use App\Application\Seo\Actions\Sitemap\GenerateAllSitemapsAction;
use Illuminate\Console\Command;

class GenerateSitemapsCommand extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate static XML sitemap files.';

    public function handle(GenerateAllSitemapsAction $action): int
    {
        $this->info('Generating sitemaps...');

        $action->execute();

        $this->info('Sitemaps generated successfully.');

        return self::SUCCESS;
    }
}
