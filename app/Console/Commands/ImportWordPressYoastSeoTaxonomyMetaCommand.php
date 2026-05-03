<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\YoastSeoTaxonomyMetaImporter;
use Illuminate\Console\Command;

class ImportWordPressYoastSeoTaxonomyMetaCommand extends Command
{
    protected $signature = 'wp:import-yoast-seo-taxonomy-meta';

    protected $description = 'Import Yoast SEO meta data from WordPress terms into Laravel seo_metas table';

    public function handle(YoastSeoTaxonomyMetaImporter $importer): int
    {
        $this->info('Importing Yoast SEO taxonomy meta...');

        $total = $importer->total();

        if ($total === 0) {
            $this->warn('No WordPress categories/tags found.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $importer->import(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();
        $this->newLine(2);

        $stats = $importer->stats();

        $this->table(
            ['Processed', 'Updated', 'Skipped', 'Missing Map', 'Failed'],
            [[
                $stats['processed'],
                $stats['updated'],
                $stats['skipped'],
                $stats['missing_map'],
                $stats['failed'],
            ]]
        );

        return self::SUCCESS;
    }
}
