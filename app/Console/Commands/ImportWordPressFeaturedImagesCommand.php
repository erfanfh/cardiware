<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\FeaturedImagesImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportWordPressFeaturedImagesCommand extends Command
{
    protected $signature = 'wp:import-featured-images';

    protected $description = 'Import WordPress featured images into Laravel posts and pages';

    public function handle(FeaturedImagesImporter $importer): int
    {
        $this->info('Importing WordPress featured images...');

        $total = $importer->total();

        if ($total === 0) {
            $this->warn('No WordPress featured images found.');

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

            logger()->error('WordPress featured images import command failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->newLine(2);
            $this->error('WordPress featured images import stopped because of an unexpected error.');
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

        $this->info('WordPress featured images import finished.');

        $stats = $importer->stats();

        return $commandFailed || $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
