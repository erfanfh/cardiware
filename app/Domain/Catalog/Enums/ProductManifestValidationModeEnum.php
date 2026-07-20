<?php

namespace App\Domain\Catalog\Enums;

enum ProductManifestValidationModeEnum: string
{
    /**
     * File integrity verification is disabled.
     */
    case NONE = 'none';

    /**
     * Every downloaded file is verified separately.
     */
    case PER_FILE = 'per_file';

    /**
     * Integrity is checked after split parts are assembled.
     */
    case AFTER_ASSEMBLY = 'after_assembly';
}
