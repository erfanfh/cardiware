<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\PostsImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportWordPressPostsCommand extends Command
{
    protected $signature = 'wp:import-posts';

    protected $description = 'Import WordPress posts into Laravel';

    public function handle(PostsImporter $importer): int
    {
        $this->info('Importing WordPress posts...');

        $total = $importer->total();

        if ($total === 0) {
            $this->warn('No WordPress posts found.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $commandFailed = false;

        try {
            $importer->import(function () use ($bar) {
                $bar->advance();
            });
        } catch (Throwable $exception) {
            $commandFailed = true;

            logger()->error('WordPress posts import command failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->newLine(2);
            $this->error('WordPress posts import stopped because of an unexpected error.');
            $this->error($exception->getMessage());
        } finally {
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
        }

        $this->info('WordPress posts import finished.');

        $stats = $importer->stats();

        return $commandFailed || $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
