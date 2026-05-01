<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\TaxonomiesImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportWordPressTaxonomiesCommand extends Command
{
    protected $signature = 'wp:import-taxonomies';

    protected $description = 'Import WordPress categories and tags into Laravel';

    public function handle(TaxonomiesImporter $importer): int
    {
        $this->info('Importing WordPress taxonomies...');

        $total = $importer->count();

        if ($total === 0) {
            $this->warn('No WordPress taxonomies found.');

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

            logger()->error('WordPress taxonomies import command failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->newLine(2);
            $this->error('WordPress taxonomies import stopped because of an unexpected error.');
            $this->error($exception->getMessage());
        } finally {
            $bar->finish();

            $stats = $importer->stats();

            $this->newLine(2);

            $this->table(
                ['Processed', 'Created', 'Synced', 'Skipped', 'Failed'],
                [[
                    $stats['processed'],
                    $stats['created'],
                    $stats['synced'],
                    $stats['skipped'],
                    $stats['failed'],
                ]]
            );
        }

        $this->info('WordPress taxonomies import finished.');

        $stats = $importer->stats();

        return $commandFailed || $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
