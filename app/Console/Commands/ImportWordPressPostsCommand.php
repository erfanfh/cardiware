<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\PostsImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWordPressPostsCommand extends Command
{
    protected $signature = 'wp:import-posts';

    protected $description = 'Import WordPress posts into Laravel';

    public function handle(PostsImporter $importer): int
    {
        $this->info('Importing WordPress posts...');

        $total = DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'post')
            ->count();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $importer->import(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();

        $this->newLine(2);
        $this->info('WordPress posts imported successfully.');

        return self::SUCCESS;
    }
}
