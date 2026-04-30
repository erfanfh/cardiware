<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\PostTaxonomiesImporter;
use Illuminate\Console\Command;

class ImportWordPressPostTaxonomiesCommand extends Command
{
    protected $signature = 'wp:import-post-taxonomies';

    protected $description = 'Import WordPress post category and tag relations';

    public function handle(PostTaxonomiesImporter $importer): int
    {
        $this->info('Importing WordPress post taxonomy relations...');

        $total = $importer->total();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $importer->import(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();

        $this->newLine(2);
        $this->info('WordPress post taxonomy relations imported successfully.');

        return self::SUCCESS;
    }
}
