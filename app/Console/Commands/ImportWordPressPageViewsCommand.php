<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\PageViewsImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportWordPressPageViewsCommand extends Command
{
    protected $signature = 'wp:import-page-views';

    protected $description = 'Import WordPress page views into Laravel posts and pages';

    public function handle(PageViewsImporter $importer): int
    {
        $this->info('Importing WordPress page views...');

        $total = $importer->total();

        if ($total === 0) {
            $this->warn('No WordPress page views found.');

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

            logger()->error('WordPress page views import command failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->newLine(2);
            $this->error('WordPress page views import stopped because of an unexpected error.');
            $this->error($exception->getMessage());
        } finally {
            $bar->finish();

            $stats = $importer->stats();

            $this->newLine(2);

            $this->table(
                ['Processed', 'Updated', 'Skipped', 'Failed'],
                [[
                    $stats['processed'],
                    $stats['updated'],
                    $stats['skipped'],
                    $stats['failed'],
                ]]
            );
        }

        $this->info('WordPress page views import finished.');

        $stats = $importer->stats();

        return $commandFailed || $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
