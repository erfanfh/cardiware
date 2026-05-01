<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\YoastSeoMetaImporter;
use Illuminate\Console\Command;

class ImportWordPressYoastSeoMetaCommand extends Command
{
    protected $signature = 'wp:import-yoast-seo-meta';

    protected $description = 'Import Yoast SEO meta data from WordPress into Laravel seo_metas table';

    public function handle(YoastSeoMetaImporter $importer): int
    {
        $this->info('Importing Yoast SEO meta...');

        $total = $importer->total();

        if ($total === 0) {
            $this->warn('No WordPress posts/pages found.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $importer->import(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Processed', 'Updated', 'Skipped', 'Missing Map', 'Failed'],
            [[
                $importer->stats()['processed'],
                $importer->stats()['updated'],
                $importer->stats()['skipped'],
                $importer->stats()['missing_map'],
                $importer->stats()['failed'],
            ]]
        );

        return self::SUCCESS;
    }
}
