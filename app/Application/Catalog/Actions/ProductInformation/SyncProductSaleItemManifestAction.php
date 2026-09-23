<?php

namespace App\Application\Catalog\Actions\ProductInformation;

use App\Domain\Catalog\Enums\ProductManifestAssemblyStrategyEnum;
use App\Domain\Catalog\Enums\ProductManifestDownloadTypeEnum;
use App\Domain\Catalog\Enums\ProductManifestValidationModeEnum;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemManifest;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use Illuminate\Support\Arr;

class SyncProductSaleItemManifestAction
{
    /**
     * Sync launcher manifest settings.
     *
     * Invalid or missing values are normalized and never prevent
     * the product from being saved.
     *
     * @param array<string, mixed> $data
     */
    public function execute(
        ProductSaleItem $saleItem,
        array           $data,
    ): void
    {
        $saleItem
            ->unsetRelation('files')
            ->load('files');

        $downloadType = $this->resolveDownloadType(
            submittedValue: Arr::get($data, 'download_type'),
            saleItem: $saleItem,
        );

        $mainExecutablePath = $this->nullableString(
            Arr::get($data, 'main_executable_path')
        );

        $requiresSider = $this->toBoolean(
            Arr::get($data, 'requires_sider', false)
        );

        $siderExecutablePath = $this->nullableString(
            Arr::get($data, 'sider_executable_path')
        );

        $supportsSider = $this->toBoolean(
            Arr::get($data, 'capabilities.supports_sider', $requiresSider)
        ) || $requiresSider;

        $requiresInjection = $this->toBoolean(
            Arr::get($data, 'requires_injection', false)
        );

        $injectDllPath = $this->nullableString(
            Arr::get($data, 'inject_dll_path')
        );

        $supportsInjection = $this->toBoolean(
            Arr::get($data, 'capabilities.supports_injection', $requiresInjection)
        ) || $requiresInjection;

        $capabilities = $this->normalizeCapabilities(
            value: Arr::get($data, 'capabilities'),
            hasFiles: $saleItem->files->isNotEmpty(),
            mainExecutablePath: $mainExecutablePath,
            supportsSider: $supportsSider,
            supportsInjection: $supportsInjection,
        );

        $contentKeyId = $this->nullableString(
            Arr::get($data, 'content_key_id')
        );

        $saleItem->manifest()->updateOrCreate(
            [],
            [
                'schema_version' => max(
                    2,
                    min(
                        65535,
                        $this->integer(
                            Arr::get($data, 'schema_version'),
                            1
                        )
                    )
                ),

                'download_type' => $downloadType->value,

                'assembly_strategy' =>
                    $downloadType === ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE
                        ? ProductManifestAssemblyStrategyEnum::ORDERED->value
                        : ProductManifestAssemblyStrategyEnum::INDEPENDENT->value,

                'archive_format' => $saleItem->files->isNotEmpty()
                    ? '7z'
                    : null,

                'archive_password' => $this->nullableString(
                    Arr::get($data, 'archive_password')
                ),

                'assembled_file_name' =>
                    $this->resolveAssembledFileName(
                        data: $data,
                        saleItem: $saleItem,
                        downloadType: $downloadType,
                    ),

                'validation_mode' => $saleItem->files->isNotEmpty()
                && $saleItem->files->every(
                    fn(ProductSaleItemFile $file): bool => preg_match('/^[a-fA-F0-9]{64}$/', (string)$file->sha256) === 1
                )
                    ? ProductManifestValidationModeEnum::PER_FILE->value
                    : ProductManifestValidationModeEnum::NONE->value,

                'install_directory' => $this->nullableString(
                    Arr::get($data, 'install_directory')
                ),
                'install_folder_name' => $this->nullableString(
                    Arr::get($data, 'install_folder_name')
                ),

                'requires_administrator' => $this->toBoolean(
                    Arr::get(
                        $data,
                        'requires_administrator',
                        false
                    )
                ),
                'has_option_file' => $this->toBoolean(
                    Arr::get($data, 'has_option_file', false)
                ),
                'option_file_folder_name' => ProductSaleItemManifest::OPTION_FILE_FOLDER_NAME,
                'package_root_mode' => 'merge_to_product_root',

                'main_executable_path' =>
                    $mainExecutablePath,

                'working_directory' => $this->nullableString(
                    Arr::get($data, 'working_directory')
                ),

                'launch_arguments' => $this->nullableString(
                    Arr::get($data, 'launch_arguments')
                ),

                'requires_sider' => $requiresSider,

                'sider_executable_path' =>
                    $siderExecutablePath,

                'sider_working_directory' =>
                    $this->nullableString(
                        Arr::get(
                            $data,
                            'sider_working_directory'
                        )
                    ),

                'sider_arguments' =>
                    $this->nullableString(
                        Arr::get($data, 'sider_arguments')
                    ),

                'run_sider_as_administrator' =>
                    $this->toBoolean(
                        Arr::get(
                            $data,
                            'run_sider_as_administrator',
                            false
                        )
                    ),

                'requires_injection' => $requiresInjection,

                'inject_dll_path' => $injectDllPath,

                // Validated properly (format, uniqueness, exact 32-byte key length,
                // vault-password grant) in UpdateProductRequest -- this method only
                // decides WHAT to persist, given that decision already passed.
                'content_key_id' => $contentKeyId,
                'content_key_value' => $this->resolveContentKeyValue(
                    saleItem: $saleItem,
                    submittedContentKeyId: $contentKeyId,
                    submittedContentKeyValue: $this->nullableString(
                        Arr::get($data, 'content_key_value')
                    ),
                ),

                // Content protection policy. Stored as plain JSON (not encrypted like
                // content_key_value above) on purpose: these are paths and digests of
                // files the customer already has, so what matters is that only an admin
                // can change them, not that they stay secret.
                'protected_folder_markers' =>
                    $this->normalizeStringList(Arr::get($data, 'protected_folder_markers')),
                'protected_exact_paths' =>
                    $this->normalizeStringList(Arr::get($data, 'protected_exact_paths')),
                'protected_excluded_paths' =>
                    $this->normalizeStringList(Arr::get($data, 'protected_excluded_paths')),
                // The admin form posts this as one JSON string (protected_modules_json)
                // rather than ~150 hidden inputs for a 50-module list; an API/artisan
                // caller can still pass a plain array as protected_modules.
                'protected_modules' => $this->normalizeProtectedModules(
                    Arr::get($data, 'protected_modules')
                        ?? $this->decodeJsonList(Arr::get($data, 'protected_modules_json'))
                ),
                // Defaults to strict when absent: for a product that declares a content
                // key, "load every module" is the setting that silently weakens
                // protection, so it must be turned off deliberately.
                'protected_strict_modules' =>
                    $this->toBoolean(Arr::get($data, 'protected_strict_modules', true)),

                'cpu_compatibility' =>
                    $this->normalizeCpuCompatibility(
                        Arr::get($data, 'cpu_compatibility')
                    ),

                'capabilities' => $capabilities,

                'install_options' =>
                    $this->normalizeInstallOptions(
                        Arr::get($data, 'install_options')
                    ),

                'runtime_notices' =>
                    $this->normalizeRuntimeNotices(
                        Arr::get($data, 'runtime_notices')
                    ),

                'metadata' => is_array(
                    Arr::get($data, 'metadata')
                )
                    ? Arr::get($data, 'metadata')
                    : [],
            ]
        );
    }

    /**
     * The admin panel redacts content_key_value to an empty string on page load and
     * only ever fills in the real value after a successful vault-password unlock (see
     * RevealContentKeyAction). So a blank submission alongside the SAME
     * content_key_id that is already stored means "left locked, unchanged" -- not
     * "clear the key" -- and must fall back to whatever is already in the database
     * instead of overwriting it with null. UpdateProductRequest's validateContentKey
     * makes the matching decision about whether a REAL change is even allowed
     * (requires the vault grant); this method only decides what to persist once that
     * decision has already passed.
     */
    private function resolveContentKeyValue(
        ProductSaleItem $saleItem,
        ?string         $submittedContentKeyId,
        ?string         $submittedContentKeyValue,
    ): ?string
    {
        if ($submittedContentKeyValue !== null) {
            return $submittedContentKeyValue;
        }

        $existing = $saleItem->manifest;

        if ($existing !== null
            && $submittedContentKeyId !== null
            && $existing->content_key_id === $submittedContentKeyId) {
            return $existing->content_key_value;
        }

        return null;
    }

    private function resolveDownloadType(
        mixed           $submittedValue,
        ProductSaleItem $saleItem,
    ): ProductManifestDownloadTypeEnum
    {
        $submittedType =
            ProductManifestDownloadTypeEnum::tryFrom(
                (string)$submittedValue
            );

        if ($submittedType !== null) {
            return $submittedType;
        }

        if ($saleItem->files->count() <= 1) {
            return ProductManifestDownloadTypeEnum::SINGLE_FILE;
        }

        $fileNames = $saleItem->files
            ->map(
                fn(ProductSaleItemFile $file): ?string => $this->nullableString($file->file_name)
                    ?: $this->resolveFileNameFromUrl($file->url)
            )
            ->filter();

        $hasSplitArchiveParts = $fileNames->contains(
            static fn(string $fileName): bool => preg_match(
                    '/\.\d{3,}$/',
                    $fileName
                ) === 1
        );

        return $hasSplitArchiveParts
            ? ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE
            : ProductManifestDownloadTypeEnum::MULTIPLE_FILES;
    }

    private function resolveArchiveFormat(
        array                           $data,
        ProductSaleItem                 $saleItem,
        ProductManifestDownloadTypeEnum $downloadType,
    ): ?string
    {
        if (
            $downloadType
            !== ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE
        ) {
            return null;
        }

        $submittedFormat = strtolower(
            trim(
                (string)Arr::get(
                    $data,
                    'archive_format',
                    ''
                )
            )
        );

        if (
            in_array(
                $submittedFormat,
                ['7z'],
                true
            )
        ) {
            return $submittedFormat;
        }

        $assembledFileName =
            $this->resolveAssembledFileName(
                data: $data,
                saleItem: $saleItem,
                downloadType: $downloadType,
            );

        if ($assembledFileName === null) {
            return null;
        }

        $extension = strtolower(
            pathinfo(
                $assembledFileName,
                PATHINFO_EXTENSION
            )
        );

        return in_array(
            $extension,
            ['7z'],
            true
        )
            ? $extension
            : null;
    }

    private function resolveAssembledFileName(
        array                           $data,
        ProductSaleItem                 $saleItem,
        ProductManifestDownloadTypeEnum $downloadType,
    ): ?string
    {
        if (
            $downloadType
            !== ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE
        ) {
            return null;
        }

        $submittedFileName = $this->nullableString(
            Arr::get($data, 'assembled_file_name')
        );

        if ($submittedFileName !== null) {
            return basename($submittedFileName);
        }

        $firstFile = $saleItem->files
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->first();

        if (!$firstFile instanceof ProductSaleItemFile) {
            return null;
        }

        $fileName = $this->nullableString($firstFile->file_name)
            ?: $this->resolveFileNameFromUrl($firstFile->url);

        if ($fileName === null) {
            return null;
        }

        return preg_replace(
            '/\.\d{3,}$/',
            '',
            $fileName
        ) ?: null;
    }

    /**
     * @return array<string, bool>
     */
    private function normalizeCapabilities(
        mixed   $value,
        bool    $hasFiles,
        ?string $mainExecutablePath,
        bool    $supportsSider,
        bool    $supportsInjection,
    ): array
    {
        $value = is_array($value) ? $value : [];

        $supportsInstall = $hasFiles
            && $this->toBoolean(
                $value['supports_install'] ?? true
            );

        $supportsLaunch = $mainExecutablePath !== null
            && $this->toBoolean(
                $value['supports_launch'] ?? true
            );

        return [
            'supports_install' => $supportsInstall,
            'supports_launch' => $supportsLaunch,
            'supports_sider' => $supportsSider,
            'supports_injection' => $supportsInjection,

            'supports_verify' => $hasFiles,
            'supports_repair' => false,
            'supports_update' => $this->toBoolean(
                $value['supports_update'] ?? false
            ),
            'supports_mod_manager' => $this->toBoolean(
                $value['supports_mod_manager'] ?? false
            ),

            'supports_settings' => $this->toBoolean(
                $value['supports_settings'] ?? false
            ),

            'supports_profiles' => $this->toBoolean(
                $value['supports_profiles'] ?? false
            ),
        ];
    }

    /**
     * @return array{enabled: bool, default_mode: string, apply_to_game: bool, apply_to_sider: bool, allow_user_override: bool}
     */
    private function normalizeCpuCompatibility(mixed $value): array
    {
        $value = is_array($value) ? $value : [];

        $defaultMode = strtolower(trim((string)($value['default_mode'] ?? 'off')));

        if (!in_array(
            $defaultMode,
            ProductSaleItemManifest::CPU_COMPATIBILITY_MODES,
            true
        )) {
            $defaultMode = ProductSaleItemManifest::DEFAULT_CPU_COMPATIBILITY['default_mode'];
        }

        return [
            'enabled' => $this->toBoolean($value['enabled'] ?? false),
            'default_mode' => $defaultMode,
            'apply_to_game' => $this->toBoolean($value['apply_to_game'] ?? false),
            'apply_to_sider' => $this->toBoolean($value['apply_to_sider'] ?? false),
            'allow_user_override' => $this->toBoolean($value['allow_user_override'] ?? false),
        ];
    }

    /**
     * @return array<string, bool|int|null>
     */
    private function normalizeInstallOptions(
        mixed $value,
    ): array
    {
        $value = is_array($value) ? $value : [];

        return [
            'extract_enabled' => $this->toBoolean(
                $value['extract_enabled'] ?? true
            ),

            'delete_archives_after_install' =>
                $this->toBoolean(
                    $value['delete_archives_after_install'] ?? true
                ),

            'ask_before_delete_archives' =>
                $this->toBoolean(
                    $value['ask_before_delete_archives'] ?? true
                ),

            'clean_temp_after_install' =>
                $this->toBoolean(
                    $value['clean_temp_after_install'] ?? true
                ),

            'create_desktop_shortcut' =>
                $this->toBoolean(
                    $value['create_desktop_shortcut'] ?? false
                ),

            'required_install_space_bytes' =>
                $this->nullablePositiveInteger(
                    $value['required_install_space_bytes'] ?? null
                ),

            'estimated_installed_size_bytes' =>
                $this->nullablePositiveInteger(
                    $value['estimated_installed_size_bytes'] ?? null
                ),
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function normalizeRuntimeNotices(
        mixed $value,
    ): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(
                static fn(mixed $notice): bool => is_array($notice)
            )
            ->map(function (array $notice): ?array {
                $message = $this->nullableString(
                    $notice['message'] ?? null
                );

                if ($message === null) {
                    return null;
                }

                $type = in_array(
                    $notice['type'] ?? null,
                    ['info', 'warning', 'error'],
                    true
                )
                    ? $notice['type']
                    : 'info';

                return [
                    'type' => $type,
                    'title' => $this->nullableString(
                        $notice['title'] ?? null
                    ),
                    'message' => $message,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function resolveFileNameFromUrl(
        string $url,
    ): ?string
    {
        $path = parse_url(
            $url,
            PHP_URL_PATH
        );

        if (!is_string($path) || $path === '') {
            return null;
        }

        $fileName = basename(
            rawurldecode($path)
        );

        return $fileName !== ''
            ? $fileName
            : null;
    }

    private function nullableString(
        mixed $value,
    ): ?string
    {
        $value = trim((string)$value);

        return $value !== ''
            ? $value
            : null;
    }

    /**
     * Trimmed, de-duplicated, re-indexed list of non-empty strings, or null when
     * nothing is left. Null rather than [] so a manifest with no exclusions reads as
     * "not configured" rather than "configured to be empty" -- the launcher and the
     * admin form both distinguish the two.
     *
     * Values containing line breaks are dropped: the policy sider receives is
     * line-oriented with no escaping, so a newline inside a value could inject an
     * extra directive. UpdateProductRequest rejects those before we get here; this is
     * the second line of defence for any path that reaches this action another way
     * (an artisan command, a seeder, a future API).
     *
     * @return list<string>|null
     */
    private function normalizeStringList(mixed $value): ?array
    {
        // The admin form sends one path per line in a textarea; an API or artisan
        // caller can send a real array. Accept both rather than forcing either side
        // to adapt.
        if (is_string($value)) {
            $value = preg_split('/\R/', $value) ?: [];
        }

        if (!is_array($value)) {
            return null;
        }

        $items = [];
        foreach ($value as $entry) {
            if (!is_string($entry)) {
                continue;
            }
            $entry = trim($entry);
            if ($entry === '' || preg_match('/[\r\n]/', $entry)) {
                continue;
            }
            $items[] = $entry;
        }

        $items = array_values(array_unique($items));

        return $items !== [] ? $items : null;
    }

    /**
     * Decodes the admin form's JSON-encoded module list. Returns null (not an empty
     * array) on anything unusable, so normalizeProtectedModules treats it as "not
     * submitted" rather than "submitted as empty" -- the difference decides whether
     * an existing list is wiped.
     */
    private function decodeJsonList(mixed $value): ?array
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @return list<array{path: string, tier: string, sha256: string}>|null
     */
    private function normalizeProtectedModules(mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        $modules = [];
        $seen = [];
        foreach ($value as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $path = trim((string)($entry['path'] ?? ''));
            $tier = strtolower(trim((string)($entry['tier'] ?? 'shipped')));
            $sha256 = strtolower(trim((string)($entry['sha256'] ?? '')));

            if ($path === '' || preg_match('/[\r\n]/', $path)) {
                continue;
            }
            // An unknown tier is dropped rather than coerced: guessing "shipped" would
            // silently grant the `memory` library, and guessing "content" would grant
            // access to decrypted content. Neither is a safe default for a typo.
            if (!in_array($tier, ['content', 'shipped'], true)) {
                continue;
            }
            if (!preg_match('/\A[a-f0-9]{64}\z/', $sha256)) {
                continue;
            }
            // Sider matches module paths case-insensitively, so two entries differing
            // only in case would be ambiguous -- keep the first.
            $key = mb_strtolower($path);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $modules[] = ['path' => $path, 'tier' => $tier, 'sha256' => $sha256];
        }

        return $modules !== [] ? $modules : null;
    }

    private function integer(
        mixed $value,
        int   $default = 0,
    ): int
    {
        return is_numeric($value)
            ? (int)$value
            : $default;
    }

    private function nullablePositiveInteger(
        mixed $value,
    ): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        return max(0, (int)$value);
    }

    private function toBoolean(
        mixed $value,
    ): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        );
    }
}
