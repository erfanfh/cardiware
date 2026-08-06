<?php

namespace App\Application\Icon\DTOs;

readonly class IconImportResult
{
    public function __construct(
        public string $setKey,
        public string $setLabel,
        public int $importedCount,
        public int $removedCount,
        public bool $skipped,
        public string $sourceFile,
    ) {
    }
}
