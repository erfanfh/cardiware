<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\CommentsImporter;
use Illuminate\Console\Command;

class ImportWordPressCommentsCommand extends Command
{
    protected $signature = 'wp:import-comments';

    protected $description = 'Import WordPress comments into Laravel';

    public function handle(CommentsImporter $importer): int
    {
        $this->info('Importing WordPress comments...');

        $total = $importer->total();

        if ($total === 0) {
            $this->warn('No WordPress comments found.');

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
            ['Processed', 'Created', 'Updated', 'Skipped', 'Missing Post Map', 'Relations Updated', 'Failed'],
            [[
                $importer->stats()['processed'],
                $importer->stats()['created'],
                $importer->stats()['updated'],
                $importer->stats()['skipped'],
                $importer->stats()['missing_post_map'],
                $importer->stats()['relations_updated'],
                $importer->stats()['failed'],
            ]]
        );

        return self::SUCCESS;
    }
}
