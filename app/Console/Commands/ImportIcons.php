<?php

namespace App\Console\Commands;

use App\Application\Icon\Actions\ImportIconSetJsonAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;

class ImportIcons extends Command
{
    protected $signature = 'icons:import
        {path? : JSON file or directory; defaults to resources/icon-sets}
        {--label= : Display label; only available when importing one file}
        {--force : Import even when the file checksum has not changed}';

    protected $description = 'Import SVG icon collections from Iconify-style JSON array files';

    public function handle(ImportIconSetJsonAction $importAction): int
    {
        try {
            $files = $this->resolveFiles($this->argument('path'));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        if ($files === []) {
            $this->warn('No JSON files were found; icon sync was skipped.');
            return self::SUCCESS;
        }

        if (filled($this->option('label')) && count($files) !== 1) {
            $this->error('The --label option can only be used when importing a single JSON file.');
            return self::FAILURE;
        }

        $failed = 0;

        foreach ($files as $file) {
            $this->newLine();
            $this->components->info('Importing ' . $file);

            try {
                $result = $importAction->execute(
                    path: $file,
                    label: $this->option('label'),
                    force: (bool)$this->option('force'),
                );

                if ($result->skipped) {
                    $this->components->twoColumnDetail(
                        $result->setLabel,
                        '<fg=yellow>unchanged; skipped</>',
                    );
                    continue;
                }

                $this->components->twoColumnDetail('Set', $result->setLabel . ' (' . $result->setKey . ')');
                $this->components->twoColumnDetail('Icons', number_format($result->importedCount));
                $this->components->twoColumnDetail('Removed stale icons', number_format($result->removedCount));
                $this->components->info('Import completed.');
            } catch (Throwable $exception) {
                report($exception);
                $failed++;
                $this->components->error($exception->getMessage());
            }
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<int, string>
     */
    private function resolveFiles(mixed $pathArgument): array
    {
        $path = filled($pathArgument)
            ? $this->resolvePath((string)$pathArgument)
            : resource_path('icon-sets');

        if (is_file($path)) {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'json') {
                throw new RuntimeException("Icon import file [$path] must have a .json extension.");
            }

            return [$path];
        }

        if (!is_dir($path)) {
            throw new RuntimeException("Icon import path [$path] does not exist.");
        }

        return collect(File::allFiles($path))
            ->filter(fn(\SplFileInfo $file) => strtolower($file->getExtension()) === 'json')
            ->map(fn(\SplFileInfo $file) => $file->getRealPath())
            ->filter()
            ->sort()
            ->values()
            ->all();
    }

    private function resolvePath(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        $fromBasePath = base_path($path);

        if (file_exists($fromBasePath)) {
            return $fromBasePath;
        }

        return resource_path('icon-sets/' . ltrim($path, '/\\'));
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
    }
}
