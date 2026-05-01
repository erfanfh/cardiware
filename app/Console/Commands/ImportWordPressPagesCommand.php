<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\PagesImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportWordPressPagesCommand extends Command
{
    protected $signature = 'wp:import-pages';

    protected $description = 'Import WordPress pages into Laravel';

    public function handle(PagesImporter $importer): int
    {
        $this->info('Importing WordPress pages...');

        $total = $importer->total();

        if ($total === 0) {
            $this->warn('No WordPress pages found.');

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

            logger()->error('WordPress pages import command failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->newLine(2);
            $this->error('WordPress pages import stopped because of an unexpected error.');
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

        $this->info('WordPress pages import finished.');

        $stats = $importer->stats();

        return $commandFailed || $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
