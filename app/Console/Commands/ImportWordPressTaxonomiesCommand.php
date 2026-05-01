<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\TaxonomiesImporter;
use Illuminate\Console\Command;

class ImportWordPressTaxonomiesCommand extends Command
{
    protected $signature = 'wp:import-taxonomies';

    protected $description = 'Import WordPress categories and tags into Laravel';

    public function handle(TaxonomiesImporter $importer): int
    {
        $this->info('Importing WordPress taxonomies...');

        $total = $importer->count();

        if ($total === 0) {
            $this->warn('No WordPress taxonomies found.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $importer->import(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();

        $this->newLine(2);
        $this->info('WordPress taxonomies imported successfully.');

        return self::SUCCESS;
    }
}
