<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProductManifestAssemblyStrategyEnum;
use App\Domain\Catalog\Enums\ProductManifestDownloadTypeEnum;
use App\Domain\Catalog\Enums\ProductManifestValidationModeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSaleItemManifest extends Model
{
    public const OPTION_FILE_FOLDER_NAME = 'OPTION FILE';

    public const OPTION_FILE_TARGET = 'windows_documents';

    public const CPU_COMPATIBILITY_MODES = [
        'off',
        'auto',
        'manual',
    ];

    public const DEFAULT_CAPABILITIES = [
        'supports_install' => true,
        'supports_launch' => true,
        'supports_sider' => false,
        'supports_verify' => false,
        'supports_repair' => false,
        'supports_update' => false,
        'supports_settings' => false,
        'supports_profiles' => false,
        'supports_mod_manager' => false,
    ];

    public const DEFAULT_CPU_COMPATIBILITY = [
        'enabled' => false,
        'default_mode' => 'off',
        'apply_to_game' => false,
        'apply_to_sider' => false,
        'allow_user_override' => false,
    ];

    public const DEFAULT_INSTALL_OPTIONS = [
        'extract_enabled' => true,
        'delete_archives_after_install' => true,
        'ask_before_delete_archives' => true,
        'clean_temp_after_install' => true,
        'create_desktop_shortcut' => false,
        'required_install_space_bytes' => null,
        'estimated_installed_size_bytes' => null,
    ];

    protected $fillable = [
        'product_sale_item_id',
        'schema_version',
        'download_type',
        'assembly_strategy',
        'archive_format',
        'archive_password',
        'assembled_file_name',
        'validation_mode',
        'install_directory',
        'install_folder_name',
        'requires_administrator',
        'has_option_file',
        'option_file_folder_name',
        'package_root_mode',
        'main_executable_path',
        'working_directory',
        'launch_arguments',
        'requires_sider',
        'sider_executable_path',
        'sider_working_directory',
        'sider_arguments',
        'run_sider_as_administrator',
        'cpu_compatibility',
        'capabilities',
        'install_options',
        'runtime_notices',
        'metadata',
    ];

    protected $casts = [
        'schema_version' => 'integer',
        'archive_password' => 'encrypted',
        'download_type' => ProductManifestDownloadTypeEnum::class,
        'assembly_strategy' => ProductManifestAssemblyStrategyEnum::class,
        'validation_mode' => ProductManifestValidationModeEnum::class,
        'requires_administrator' => 'boolean',
        'has_option_file' => 'boolean',
        'requires_sider' => 'boolean',
        'run_sider_as_administrator' => 'boolean',
        'cpu_compatibility' => 'array',
        'capabilities' => 'array',
        'install_options' => 'array',
        'runtime_notices' => 'array',
        'metadata' => 'array',
    ];

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id');
    }

    /**
     * @return array<string, bool>
     */
    public function resolvedCapabilities(): array
    {
        $capabilities = array_replace(
            self::DEFAULT_CAPABILITIES,
            $this->capabilities ?? []
        );

        if ($this->requires_sider) {
            $capabilities['supports_sider'] = true;
        }

        return $capabilities;
    }

    /**
     * @return array{enabled: bool, default_mode: string, apply_to_game: bool, apply_to_sider: bool, allow_user_override: bool}
     */
    public function resolvedCpuCompatibility(): array
    {
        $cpuCompatibility = array_replace(
            self::DEFAULT_CPU_COMPATIBILITY,
            $this->cpu_compatibility ?? []
        );

        $defaultMode = strtolower(trim((string) $cpuCompatibility['default_mode']));

        $cpuCompatibility['default_mode'] = in_array(
            $defaultMode,
            self::CPU_COMPATIBILITY_MODES,
            true
        ) ? $defaultMode : self::DEFAULT_CPU_COMPATIBILITY['default_mode'];

        return $cpuCompatibility;
    }

    /**
     * OPTION FILE is a package contract, not a free-form path.
     * When enabled, the launcher must find this folder at the extracted
     * package root before installation can continue.
     *
     * @return array{enabled: bool, folder_name: string, target: string}
     */
    public function resolvedOptionFile(): array
    {
        return [
            'enabled' => (bool) $this->has_option_file,
            'folder_name' => self::OPTION_FILE_FOLDER_NAME,
            'target' => self::OPTION_FILE_TARGET,
        ];
    }

    /**
     * @return array<string, bool|int|null>
     */
    public function resolvedInstallOptions(): array
    {
        return array_replace(self::DEFAULT_INSTALL_OPTIONS, $this->install_options ?? []);
    }
}
