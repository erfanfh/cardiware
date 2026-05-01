<?php

namespace App\Console\Commands;

use App\Application\WordPressMigration\Importers\SyncWordPressMediaFilesImporter;
use Illuminate\Console\Command;

class SyncWordPressMediaFilesCommand extends Command
{
    protected $signature = 'wp:sync-media-files';

    protected $description = 'Download missing WordPress media files and sync WordPress image variants';

    public function handle(SyncWordPressMediaFilesImporter $importer): int
    {
        $this->info('Syncing WordPress media files and variants...');

        $bar = $this->output->createProgressBar($importer->total());
        $bar->start();

        $importer->import(function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();

        $stats = $importer->stats();

        $this->newLine(2);

        $this->table(
            [
                'Processed',
                'Missing Map',
                'Missing File Meta',
                'Original Downloaded',
                'Variant Downloaded',
                'Original Variants Created',
                'Image Variants Created',
                'Variants Updated',
                'Unsupported',
                'Download Failed',
                'Skipped',
                'Failed',
            ],
            [[
                $stats['processed'],
                $stats['missing_map'],
                $stats['missing_attached_file'],
                $stats['original_downloaded'],
                $stats['variant_downloaded'],
                $stats['original_variants_created'],
                $stats['image_variants_created'],
                $stats['variants_updated'],
                $stats['unsupported_format'],
                $stats['download_failed'],
                $stats['skipped'],
                $stats['failed'],
            ]]
        );

        $this->info('WordPress media sync finished.');

        return $stats['failed'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
