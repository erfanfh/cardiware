<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\UsersImporter;
use Illuminate\Console\Command;

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

        $importer->import(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();

        $this->newLine(2);
        $this->info('WordPress users imported successfully.');

        return self::SUCCESS;
    }
}
