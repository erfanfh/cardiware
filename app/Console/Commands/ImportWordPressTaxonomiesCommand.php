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

        $importer->import();

        $this->info('WordPress taxonomies imported successfully.');

        return self::SUCCESS;
    }
}
