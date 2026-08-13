<?php

namespace App\Application\Seo\Actions\Redirect;

use App\Application\Shared\Cache\Invalidators\SeoRedirectCacheInvalidator;
use App\Domain\Seo\Enums\RedirectStatusCodeEnum;
use App\Domain\Seo\Models\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SplFileObject;

class ImportYoastRedirectsAction
{
    public function __construct(
        private readonly NormalizeRedirectPathAction $normalizeRedirectPathAction,
        private readonly SeoRedirectCacheInvalidator $cacheInvalidator,
    )
    {
    }

    public function execute(string $path, ?callable $progress = null): array
    {
        if (!Storage::disk('local')->exists($path)) {
            throw new RuntimeException("Redirect import file not found: {$path}");
        }

        $file = new SplFileObject(Storage::disk('local')->path($path));
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

        $headers = null;

        $stats = [
            'processed' => 0,
            'imported' => 0,
            'skipped' => 0,
            'invalid' => 0,
            'gone' => 0,
            'redirects' => 0,
        ];

        DB::transaction(function () use ($file, &$headers, &$stats, $progress) {
            foreach ($file as $row) {
                if ($row === [null] || $row === false) {
                    continue;
                }

                $row = array_map(
                    fn($value) => is_string($value) ? trim($value) : $value,
                    $row
                );

                if ($headers === null) {
                    $headers = $this->normalizeHeaders($row);
                    continue;
                }

                $data = array_combine($headers, array_pad($row, count($headers), null));

                if (!is_array($data)) {
                    $stats['invalid']++;
                    continue;
                }

                $stats['processed']++;

                $source = $this->normalizeRedirectPathAction->execute($data['source'] ?? null);
                $target = trim((string)($data['target'] ?? ''));
                $statusCode = (int)($data['status_code'] ?? 0);
                $format = trim((string)($data['format'] ?? 'plain')) ?: 'plain';

                $status = RedirectStatusCodeEnum::tryFrom($statusCode);

                if (!$status || $source === '/') {
                    $stats['invalid']++;
                    continue;
                }

                if ($status->isRedirect() && $target === '') {
                    $stats['invalid']++;
                    continue;
                }

                Redirect::query()->updateOrCreate(
                    ['source_path' => $source],
                    [
                        'target_url' => $status->isGone() ? null : $target,
                        'status_code' => $status,
                        'format' => $format,
                        'is_active' => true,
                    ]
                );

                $status->isGone()
                    ? $stats['gone']++
                    : $stats['redirects']++;

                $stats['imported']++;

                if ($progress !== null) {
                    $progress($stats);
                }
            }
        });

        $this->cacheInvalidator->invalidate();

        return $stats;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function (?string $header): string {
            return match (trim((string)$header)) {
                'منشاء' => 'source',
                'هدف' => 'target',
                'نوع' => 'status_code',
                'قالب' => 'format',
                default => trim((string)$header),
            };
        }, $headers);
    }
}
