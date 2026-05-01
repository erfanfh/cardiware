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

        $stats = $importer->stats();

        $this->newLine(2);

        $this->table(
            ['Processed', 'Created', 'Reused', 'Skipped', 'Unsupported', 'Failed'],
            [[
                $stats['processed'],
                $stats['created'],
                $stats['reused'],
                $stats['skipped'],
                $stats['unsupported'],
                $stats['failed'],
            ]]
        );

        $this->info('WordPress attachments import finished.');

        return $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
