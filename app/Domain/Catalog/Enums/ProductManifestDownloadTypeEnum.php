<?php

namespace App\Domain\Catalog\Enums;

enum ProductManifestDownloadTypeEnum: string
{
    /**
     * A single downloadable file.
     */
    case SINGLE_FILE = 'single_file';

    /**
     * Multiple independent files.
     */
    case MULTIPLE_FILES = 'multiple_files';

    /**
     * Multiple parts forming one archive.
     */
    case SPLIT_ARCHIVE = 'split_archive';
}
