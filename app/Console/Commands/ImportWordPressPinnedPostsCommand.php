<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\PinnedPostsImporter;
use Illuminate\Console\Command;

class ImportWordPressPinnedPostsCommand extends Command
{
    protected $signature = 'wp:import-pinned-posts';

    protected $description = 'Import WordPress sticky posts into Laravel pinned posts';

    public function handle(PinnedPostsImporter $importer): int
    {
        $this->info('Importing WordPress pinned posts...');

        $total = $importer->total();

        if ($total === 0) {
            $this->warn('No pinned posts found.');

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
            ['Processed', 'Pinned', 'Missing Map', 'Failed'],
            [[
                $importer->stats()['processed'],
                $importer->stats()['pinned'],
                $importer->stats()['missing_map'],
                $importer->stats()['failed'],
            ]]
        );

        $this->info('Pinned posts import completed.');

        return self::SUCCESS;
    }
}
