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

        $this->call('optimize:clear');
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--force' => true,
        ]);
        $this->call('horizon:terminate');
        $this->info('✅ Deploy finalized.');
    }
}
