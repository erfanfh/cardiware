<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\PostFeaturesImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportWordPressPostFeaturesCommand extends Command
{
    protected $signature = 'wp:import-post-features';

    protected $description = 'Import WordPress post feature flags and front badges';

    public function handle(PostFeaturesImporter $importer): int
    {
        $this->info('Importing WordPress post features...');

        $total = $importer->total();

        if ($total === 0) {
            $this->warn('No WordPress post features found.');

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

            logger()->error('WordPress post features import command failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->newLine(2);
            $this->error('WordPress post features import stopped because of an unexpected error.');
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

        $this->info('WordPress post features import finished.');

        $stats = $importer->stats();

        return $commandFailed || $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
