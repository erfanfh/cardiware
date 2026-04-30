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

        $importer->import();

        $this->info('WordPress users imported successfully.');

        return self::SUCCESS;
    }
}
