<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;

class Deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this command after deploy!';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('🚀 Running post-deploy commands...');

        $commands = [
            'migrate',
            DatabaseSeeder::class,
            'optimize:clear',
            'optimize',
            'queue:restart',
        ];

        foreach ($commands as $command) {
            $this->call($command);
        }

        $this->info('✅ Deploy finalized.');
    }
}
