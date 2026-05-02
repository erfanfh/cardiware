<?php

namespace App\Console\Commands;

use App\Application\Seo\Actions\Redirect\ImportYoastRedirectsAction;
use Illuminate\Console\Command;

class ImportYoastRedirectsCommand extends Command
{
    protected $signature = 'seo:import-yoast-redirects
                            {path=imports/wordpress-seo-redirects.csv : Path inside storage/app}';

    protected $description = 'Import Yoast SEO Premium redirects from CSV export.';

    public function handle(ImportYoastRedirectsAction $action): int
    {
        $path = $this->argument('path');

        $this->info("Importing Yoast redirects from storage/app/{$path}");

        $stats = $action->execute($path);

        $this->table(
            ['Processed', 'Imported', 'Redirects', 'Gone', 'Invalid', 'Skipped'],
            [[
                $stats['processed'],
                $stats['imported'],
                $stats['redirects'],
                $stats['gone'],
                $stats['invalid'],
                $stats['skipped'],
            ]]
        );

        $this->info('Yoast redirects imported successfully.');

        return self::SUCCESS;
    }
}
