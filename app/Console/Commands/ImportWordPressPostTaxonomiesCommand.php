<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\PostTaxonomiesImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportWordPressPostTaxonomiesCommand extends Command
{
    protected $signature = 'wp:import-post-taxonomies';

    protected $description = 'Import WordPress post category and tag relations';

    public function handle(PostTaxonomiesImporter $importer): int
    {
        $this->info('Importing WordPress post taxonomy relations...');

        $total = $importer->total();

        if ($total === 0) {
            $this->warn('No WordPress post taxonomy relations found.');

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

            logger()->error('WordPress post taxonomy relations import command failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->newLine(2);
            $this->error('WordPress post taxonomy relations import stopped because of an unexpected error.');
            $this->error($exception->getMessage());
        } finally {
            $bar->finish();

            $stats = $importer->stats();

            $this->newLine(2);

            $this->table(
                ['Processed', 'Attached', 'Skipped', 'Failed'],
                [[
                    $stats['processed'],
                    $stats['attached'],
                    $stats['skipped'],
                    $stats['failed'],
                ]]
            );
        }

        $this->info('WordPress post taxonomy relations import finished.');

        $stats = $importer->stats();

        return $commandFailed || $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
