<?php

namespace App\Application\Icon\Actions;

use App\Application\Icon\DTOs\IconImportResult;
use App\Domain\Icon\Models\IconSet;
use App\Infrastructure\Icon\JsonArrayObjectStream;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ImportIconSetJsonAction
{
    private const UPSERT_CHUNK_SIZE = 500;

    public function __construct(
        protected JsonArrayObjectStream $stream,
        protected SanitizeIconSvgAction $sanitizeSvg,
    ) {
    }

    public function execute(
        string $path,
        ?string $label = null,
        bool $force = false,
    ): IconImportResult {
        $realPath = realpath($path);

        if ($realPath === false || !is_file($realPath) || !is_readable($realPath)) {
            throw new RuntimeException("Icon JSON file [$path] does not exist or is not readable.");
        }

        $checksum = hash_file('sha256', $realPath);

        if (!is_string($checksum)) {
            throw new RuntimeException("Could not calculate checksum for icon JSON file [$realPath].");
        }

        $entries = $this->stream->read($realPath);
        $entries->rewind();

        if (!$entries->valid()) {
            throw new RuntimeException("Icon JSON file [$realPath] does not contain any icons.");
        }

        [$setKey] = $this->parseFullName(
            fullName: $this->entryName($entries->current(), 0, $realPath),
            index: 0,
            path: $realPath,
        );

        $setLabel = filled($label)
            ? trim((string)$label)
            : Str::headline(str_replace(['-', '_'], ' ', $setKey));

        $iconSet = IconSet::query()->firstOrNew(['key' => $setKey]);

        if (
            $iconSet->exists
            && $iconSet->checksum === $checksum
            && !$force
            && (!filled($label) || $iconSet->label === $setLabel)
        ) {
            return new IconImportResult(
                setKey: $iconSet->key,
                setLabel: $iconSet->label,
                importedCount: $iconSet->icons_count,
                removedCount: 0,
                skipped: true,
                sourceFile: basename($realPath),
            );
        }

        $iconSet->fill([
            'label' => $setLabel,
            'source_file' => basename($realPath),
        ])->save();

        $batch = (string)Str::uuid();
        $rows = [];
        $importedCount = 0;

        foreach ($entries as $index => $entry) {
            $fullName = $this->entryName($entry, $index, $realPath);
            [$entrySetKey, $iconName] = $this->parseFullName($fullName, $index, $realPath);
            $fullName = $entrySetKey . ':' . $iconName;

            if ($entrySetKey !== $setKey) {
                throw new RuntimeException(
                    "Icon JSON file [$realPath] contains more than one icon set: [$setKey] and [$entrySetKey]."
                );
            }

            $svg = $entry['svg'] ?? null;

            if (!is_string($svg) || trim($svg) === '') {
                throw new RuntimeException("Icon entry [$index] in [$realPath] does not contain valid SVG markup.");
            }

            $rows[$fullName] = [
                'icon_set_id' => $iconSet->id,
                'name' => $iconName,
                'full_name' => $fullName,
                'search_text' => $this->buildSearchText($setKey, $iconName),
                'svg' => $this->sanitizeSvg->execute($svg),
                'import_batch' => $batch,
            ];

            if (count($rows) >= self::UPSERT_CHUNK_SIZE) {
                $importedCount += $this->upsertRows(array_values($rows));
                $rows = [];
            }
        }

        if ($rows !== []) {
            $importedCount += $this->upsertRows(array_values($rows));
        }

        if ($importedCount === 0) {
            throw new RuntimeException("Icon JSON file [$realPath] did not produce any importable icons.");
        }

        $removedCount = DB::table('icons')
            ->where('icon_set_id', $iconSet->id)
            ->where('import_batch', '!=', $batch)
            ->delete();

        $actualCount = DB::table('icons')
            ->where('icon_set_id', $iconSet->id)
            ->count();

        $iconSet->update([
            'label' => $setLabel,
            'source_file' => basename($realPath),
            'checksum' => $checksum,
            'icons_count' => $actualCount,
            'imported_at' => now(),
        ]);

        return new IconImportResult(
            setKey: $setKey,
            setLabel: $setLabel,
            importedCount: $actualCount,
            removedCount: $removedCount,
            skipped: false,
            sourceFile: basename($realPath),
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function upsertRows(array $rows): int
    {
        DB::table('icons')->upsert(
            $rows,
            ['full_name'],
            ['icon_set_id', 'name', 'search_text', 'svg', 'import_batch'],
        );

        return count($rows);
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function entryName(array $entry, int $index, string $path): string
    {
        $name = $entry['name'] ?? null;

        if (!is_string($name) || trim($name) === '') {
            throw new RuntimeException("Icon entry [$index] in [$path] does not contain a valid name.");
        }

        return trim($name);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseFullName(string $fullName, int $index, string $path): array
    {
        if (!str_contains($fullName, ':')) {
            throw new RuntimeException(
                "Icon name [$fullName] at index [$index] in [$path] must use the [set:name] format."
            );
        }

        [$setKey, $iconName] = array_map('trim', explode(':', $fullName, 2));

        if ($setKey === '' || $iconName === '') {
            throw new RuntimeException(
                "Icon name [$fullName] at index [$index] in [$path] must use the [set:name] format."
            );
        }

        if (mb_strlen($setKey) > 100 || mb_strlen($iconName) > 190 || mb_strlen($fullName) > 255) {
            throw new RuntimeException("Icon name [$fullName] at index [$index] in [$path] is too long.");
        }

        if (preg_match('/^[a-z0-9][a-z0-9._-]*$/i', $setKey) !== 1) {
            throw new RuntimeException("Icon set key [$setKey] at index [$index] in [$path] is invalid.");
        }

        return [strtolower($setKey), $iconName];
    }

    private function buildSearchText(string $setKey, string $iconName): string
    {
        return str("{$setKey} {$iconName}")
            ->lower()
            ->replace([':', '-', '_', '.'], ' ')
            ->squish()
            ->limit(255, '')
            ->toString();
    }
}
