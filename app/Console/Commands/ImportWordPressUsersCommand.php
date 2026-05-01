<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\UsersImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportWordPressUsersCommand extends Command
{
    protected $signature = 'wp:import-users';

    protected $description = 'Import WordPress users into Laravel';

    public function handle(UsersImporter $importer): int
    {
        $this->info('Importing WordPress users...');

        $total = $importer->count();

        if ($total === 0) {
            $this->warn('No WordPress users found.');

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

            logger()->error('WordPress users import command failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->newLine(2);
            $this->error('WordPress users import stopped because of an unexpected error.');
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

        $this->info('WordPress users import finished.');

        $stats = $importer->stats();

        return $commandFailed || $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
