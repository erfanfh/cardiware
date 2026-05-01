<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\PostsImporter;
use Illuminate\Console\Command;

class ImportWordPressPostsCommand extends Command
{
    protected $signature = 'wp:import-posts';

    protected $description = 'Import WordPress posts into Laravel';

    public function handle(PostsImporter $importer): int
    {
        $this->info('Importing WordPress posts...');

        $total = $importer->total();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $importer->import(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();

        $stats = $importer->stats();

        $this->newLine(2);

        $this->table(
            ['Processed', 'Created', 'Skipped', 'Failed'],
            [[
                $stats['processed'],
                $stats['created'],
                $stats['skipped'],
                $stats['failed'],
            ]]
        );

        $this->info('WordPress posts import finished.');

        return $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
