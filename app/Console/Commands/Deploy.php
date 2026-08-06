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
    public function handle(): int
    {
        $this->info('🚀 Running post-deploy commands...');

        $this->call('optimize:clear');
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--force' => true,
        ]);

        $this->info('🎨 Syncing icon library...');

        if ($this->call('icons:import') !== self::SUCCESS) {
            $this->error('Icon library sync failed.');
            return self::FAILURE;
        }

        $this->info('📚 Generating API documentation...');
        $this->call('scribe:generate');

        $this->call('horizon:terminate');

        $this->info('✅ Deploy finalized.');

        return self::SUCCESS;
    }
}
