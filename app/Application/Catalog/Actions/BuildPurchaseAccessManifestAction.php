<?php

namespace App\Application\Catalog\Actions;

use App\Application\Catalog\Exceptions\ProductManifestUnavailableException;
use App\Domain\Catalog\Enums\ProductManifestDownloadTypeEnum;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BuildPurchaseAccessManifestAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(
        PurchaseAccess $purchaseAccess,
    ): array
    {
        $purchaseAccess->loadMissing([
            'product:id,title,slug',
            'productSaleItem:id,product_id,sku,title,edition_key,version,manifest_revision,launcher_title,launcher_excerpt',
            'productSaleItem.manifest',
            'productSaleItem.files' => function (
                HasMany $relation,
            ): void {
                $relation
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
        ]);

        $product = $purchaseAccess->product;
        $saleItem = $purchaseAccess->productSaleItem;

        if ($product === null || $saleItem === null) {
            throw new ProductManifestUnavailableException(
                'The purchase access does not have a valid product or sale item.'
            );
        }

        $manifest = $saleItem->manifest;

        if ($manifest === null) {
            throw new ProductManifestUnavailableException(
                'No manifest settings are configured for the sale item.'
            );
        }

        if (blank($saleItem->version)) {
            throw new ProductManifestUnavailableException(
                'The sale item version has not been configured.'
            );
        }

        $capabilities = $manifest->resolvedCapabilities();
        $installOptions = $manifest->resolvedInstallOptions();
        $cpuCompatibility = $manifest->resolvedCpuCompatibility();

        $files = $this->sortFiles(
            $saleItem->files,
            $manifest->download_type
        );

        $this->validateManifest(
            files: $files,
            manifest: $manifest,
            capabilities: $capabilities,
            installOptions: $installOptions,
        );

        $isSplitArchive = $manifest->download_type
            === ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE;

        $totalSizeBytes = $isSplitArchive
            ? (int)$files->sum(
                static fn(ProductSaleItemFile $file): int => (int)$file->size_bytes
            )
            : ($files->every(
                static fn(ProductSaleItemFile $file): bool => $file->size_bytes !== null
            )
                ? (int)$files->sum('size_bytes')
                : null);

        return [
            'schema_version' => $manifest->schema_version,
            'manifest_revision' => $saleItem->manifest_revision,
            'manifest_id' => sprintf(
                '%s+%d',
                $saleItem->version,
                $saleItem->manifest_revision,
            ),
            'product' => [
                'id' => $product->id,
                'slug' => $product->slug,
                'title' => $product->title,
            ],
            'access' => [
                'id' => $purchaseAccess->id,
                'is_usable' => true,
                'starts_at' => $purchaseAccess->starts_at?->toISOString(),
                'expires_at' => $purchaseAccess->expires_at?->toISOString(),
            ],
            'sale_item' => [
                'id' => $saleItem->id,
                'sku' => $saleItem->sku,
                'title' => $saleItem->launcher_title ?: $saleItem->title,
                'excerpt' => $saleItem->launcher_excerpt,
                'edition_key' => $saleItem->edition_key,
                'version' => $saleItem->version,
            ],
            'capabilities' => $capabilities,
            'download' => [
                'type' => $manifest->download_type->value,
                'assembly_strategy' => $manifest->assembly_strategy->value,
                'archive_format' => $manifest->archive_format,
                'archive_password' => $manifest->archive_password,
                'assembled_file_name' => $manifest->assembled_file_name,
                'validation_mode' => $manifest->validation_mode->value,
                'total_size_bytes' => $totalSizeBytes,

                /*
                 * No direct or temporary URLs are returned here.
                 * URLs are resolved using the download preparation API.
                 */
                'files' => $files
                    ->map(
                        fn(ProductSaleItemFile $file): array => [
                            'file_id' => $file->id,

                            'title' => $file->title,

                            'file_name' => $this->fileName($file),
                            'size_bytes' => $file->size_bytes,
                            'sha256' => strtolower((string)$file->sha256),
                            'mime_type' => $file->mime_type,
                            'part_number' => $file->part_number,
                            'archive_group' => $file->archive_group,
                            'is_required' => $isSplitArchive
                                ? true
                                : (bool)$file->is_required,

                            /*
                             * sort_order is normalized to the split part
                             * sequence when multipart files are saved.
                             */
                            'order' => $file->sort_order,
                        ]
                    )
                    ->values()
                    ->all(),
            ],

            'installation' => [
                'install_folder_name' => $manifest->install_folder_name
                    ?: $manifest->install_directory,
                'requires_administrator' => $manifest->requires_administrator,
                'merge_mode' => $manifest->package_root_mode,
                'option_file' => $manifest->resolvedOptionFile(),
                ...$installOptions,
                /*
                 * The launcher client contract uses installed_size_bytes /
                 * temporary_size_bytes; the admin form's internal option keys
                 * (estimated_installed_size_bytes / required_install_space_bytes)
                 * are aliased here so a configured value actually reaches the
                 * client instead of silently falling back to its own estimate.
                 */
                'installed_size_bytes' => (int)($installOptions['estimated_installed_size_bytes'] ?? 0),
                'temporary_size_bytes' => (int)($installOptions['required_install_space_bytes'] ?? 0),
            ],

            'launch' => [
                'main_executable_path' => $manifest->main_executable_path,
                'working_directory' => $manifest->working_directory,
                'arguments' => $manifest->launch_arguments,
                'requires_sider' => $manifest->requires_sider,
                'sider_executable_path' => $manifest->sider_executable_path,
                'sider_working_directory' => $manifest->sider_working_directory,
                'sider_arguments' => $manifest->sider_arguments,
                'run_sider_as_administrator' => $manifest->run_sider_as_administrator,

                /*
                 * Opaque, versioned identifier only -- never the key value itself. The
                 * launcher looks this up against the content_keys map inside the signed
                 * offline-license payload (see IssueOfflineLicenseAction), which is only
                 * ever returned to the entitled user's own launcher, never embedded in
                 * this general-purpose manifest response.
                 */
                'content_key_id' => $manifest->content_key_id,

                'cpu_compatibility' => $cpuCompatibility,
            ],

            'runtime_notices' => $manifest->runtime_notices ?? [],

            'metadata' => $manifest->metadata ?: new \stdClass(),
        ];
    }

    /**
     * @param Collection<int, ProductSaleItemFile> $files
     *
     * @return Collection<int, ProductSaleItemFile>
     */
    private function sortFiles(
        Collection                      $files,
        ProductManifestDownloadTypeEnum $downloadType,
    ): Collection
    {
        if ($downloadType !== ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE) {
            return $files
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();
        }

        return $files
            ->sort(
                static fn(
                    ProductSaleItemFile $left,
                    ProductSaleItemFile $right,
                ): int => [
                        $left->part_number ?? PHP_INT_MAX,
                        $left->sort_order,
                        $left->id,
                    ] <=> [
                        $right->part_number ?? PHP_INT_MAX,
                        $right->sort_order,
                        $right->id,
                    ]
            )
            ->values();
    }

    /**
     * @param Collection<int, ProductSaleItemFile> $files
     * @param array<string, bool> $capabilities
     * @param array<string, bool|int|null> $installOptions
     */
    private function validateManifest(
        Collection $files,
        mixed      $manifest,
        array      $capabilities,
        array      $installOptions,
    ): void
    {
        if ($capabilities['supports_install'] && $files->isEmpty()) {
            throw new ProductManifestUnavailableException(
                'Install is enabled, but the sale item has no files.'
            );
        }

        if ($capabilities['supports_install']
            && blank($manifest->install_folder_name)
            && blank($manifest->install_directory)) {
            throw new ProductManifestUnavailableException(
                'Install is enabled, but the install folder name is missing.'
            );
        }

        if ($manifest->has_option_file) {
            if (!$capabilities['supports_install']) {
                throw new ProductManifestUnavailableException(
                    'OPTION FILE is enabled, but installation support is disabled.'
                );
            }

            if ($files->isEmpty()) {
                throw new ProductManifestUnavailableException(
                    'OPTION FILE is enabled, but the base package has no files.'
                );
            }

            if (!(bool) ($installOptions['extract_enabled'] ?? false)) {
                throw new ProductManifestUnavailableException(
                    'OPTION FILE requires package extraction to be enabled.'
                );
            }
        }

        if ($capabilities['supports_launch'] && blank($manifest->main_executable_path)) {
            throw new ProductManifestUnavailableException(
                'Launch is enabled, but the main executable path is missing.'
            );
        }

        if ($capabilities['supports_sider']) {
            if (blank($manifest->sider_executable_path)) {
                throw new ProductManifestUnavailableException(
                    'Sider is supported, but its executable path is missing.'
                );
            }

            if (blank($manifest->sider_working_directory)) {
                throw new ProductManifestUnavailableException(
                    'Sider is supported, but its working directory is missing.'
                );
            }
        }

        foreach ($files as $file) {
            $declaredFileName = trim((string) $file->file_name);

            if (blank($declaredFileName)) {
                throw new ProductManifestUnavailableException(
                    "File {$file->id} does not have a file name."
                );
            }

            if (basename(str_replace('\\', '/', $declaredFileName)) !== $declaredFileName) {
                throw new ProductManifestUnavailableException(
                    "File {$file->id} must use a base file name without a path."
                );
            }

            if ($file->size_bytes === null || (int) $file->size_bytes <= 0) {
                throw new ProductManifestUnavailableException(
                    "File {$file->id} does not have a valid positive size."
                );
            }

            if (!preg_match('/^[a-fA-F0-9]{64}$/', (string) $file->sha256)) {
                throw new ProductManifestUnavailableException(
                    "File {$file->id} does not have a valid SHA-256 hash."
                );
            }

            if (blank($file->url)) {
                throw new ProductManifestUnavailableException(
                    "File {$file->id} does not have a download source."
                );
            }

            if ((bool)config('launcher.require_source_file_name_match', true)) {
                $sourceFileName = basename(str_replace(
                    '\\',
                    '/',
                    rawurldecode((string)parse_url((string)$file->url, PHP_URL_PATH))
                ));

                if ($sourceFileName === '' || !hash_equals($sourceFileName, $declaredFileName)) {
                    throw new ProductManifestUnavailableException(
                        "File {$file->id} name does not match its remote source name."
                    );
                }
            }
        }

        if ($manifest->download_type !== ProductManifestDownloadTypeEnum::SPLIT_ARCHIVE) {
            return;
        }

        if ($files->isEmpty()) {
            throw new ProductManifestUnavailableException(
                'Split archive package does not have any active parts.'
            );
        }

        if (blank($manifest->archive_format)) {
            throw new ProductManifestUnavailableException(
                'Split archive format has not been configured.'
            );
        }

        if (strtolower((string)$manifest->archive_format) !== '7z') {
            throw new ProductManifestUnavailableException(
                'MW Launcher only accepts 7z packages.'
            );
        }

        if (blank($manifest->assembled_file_name)) {
            throw new ProductManifestUnavailableException(
                'The assembled archive file name is missing.'
            );
        }

        $assembledFileName = trim((string) $manifest->assembled_file_name);
        if (basename(str_replace('\\', '/', $assembledFileName)) !== $assembledFileName) {
            throw new ProductManifestUnavailableException(
                'The assembled archive name must be a base file name without a path.'
            );
        }

        foreach ($files as $file) {
            if ($file->size_bytes === null || (int)$file->size_bytes <= 0) {
                throw new ProductManifestUnavailableException(
                    "Split archive file {$file->id} does not have a valid size."
                );
            }
        }

        $partNumbers = $files
            ->pluck('part_number')
            ->map(static fn(mixed $partNumber): ?int => $partNumber !== null
                ? (int)$partNumber
                : null)
            ->values();

        if ($partNumbers->contains(null)) {
            throw new ProductManifestUnavailableException(
                'Every split archive file must have a part number.'
            );
        }

        $expectedPartNumbers = collect(range(1, $files->count()));

        if ($partNumbers->all() !== $expectedPartNumbers->all()) {
            throw new ProductManifestUnavailableException(
                'Split archive part numbers must be unique and contiguous, starting from 1.'
            );
        }

        $multipartBaseName = null;

        foreach ($files as $file) {
            $fileName = trim((string)$file->file_name);
            $matches = [];

            if (preg_match('/^(.*\.7z)\.(\d{3,})$/i', $fileName, $matches) !== 1) {
                throw new ProductManifestUnavailableException(
                    "Split archive file {$file->id} must use a multipart name such as package.7z.001."
                );
            }

            $baseName = strtolower($matches[1]);
            $multipartBaseName ??= $baseName;

            if ($multipartBaseName !== $baseName) {
                throw new ProductManifestUnavailableException(
                    'Every split archive part must use the same base file name.'
                );
            }

            if ((int)$matches[2] !== (int)$file->part_number) {
                throw new ProductManifestUnavailableException(
                    "Split archive file {$file->id} suffix does not match its part number."
                );
            }
        }
    }

    private function fileName(ProductSaleItemFile $file): string
    {
        $fileName = trim((string)(
            $file->file_name
            ?: ($file->title ?? "file-{$file->id}")
        ));

        return basename(str_replace('\\', '/', $fileName));
    }
}
