<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\FeaturedImagesImporter;
use Illuminate\Console\Command;

class ImportWordPressFeaturedImagesCommand extends Command
{
    protected $signature = 'wp:import-featured-images';

    protected $description = 'Import WordPress featured images into Laravel posts';

    public function handle(FeaturedImagesImporter $importer): int
    {
        $this->info('Importing WordPress featured images...');

        $bar = $this->output->createProgressBar($importer->total());
        $bar->start();

        $importer->import(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();

        $this->newLine(2);
        $this->info('WordPress featured images imported successfully.');

        return self::SUCCESS;
    }
}
