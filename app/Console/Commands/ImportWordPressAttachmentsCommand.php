<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\AttachmentsImporter;
use Illuminate\Console\Command;

class ImportWordPressAttachmentsCommand extends Command
{
    protected $signature = 'wp:import-attachments';

    protected $description = 'Import WordPress attachments into Laravel media library';

    public function handle(AttachmentsImporter $importer): int
    {
        $this->info('Importing WordPress attachments...');

        $bar = $this->output->createProgressBar($importer->total());
        $bar->start();

        $importer->import(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();

        $this->newLine(2);
        $this->info('WordPress attachments imported successfully.');

        return self::SUCCESS;
    }
}
