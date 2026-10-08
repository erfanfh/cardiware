<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domain\Catalog\Enums\ProductContentTypeEnum;
use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Catalog\Models\ProductSaleItemManifest;
use App\Domain\Launcher\Enums\LauncherModInstallTargetEnum;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    private const IRAN_PLAYER_STRENGTH_GROUP_KEY = 'iran_player_strength';

    private const IRAN_PLAYER_STRENGTH_GROUP_TITLE = 'سطح قدرت بازیکنان';

    private const IRAN_PLAYER_STRENGTH_MOD_NAMES = [
        'سطح عادی',
        'سطح پیشرفته',
        'سطح حرفه‌ای',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
            ],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'status' => ['required', Rule::enum(ProductStatusEnum::class)],
            'type' => ['required', Rule::enum(ProductTypeEnum::class)],
            'content_type' => ['nullable', Rule::enum(ProductContentTypeEnum::class)],
            'structure' => ['required', Rule::enum(ProductStructureEnum::class)],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'seller_commission_percent' => ['sometimes', 'required', 'numeric', 'between:0,100', 'decimal:0,2'],
            'picture_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'published_at' => ['nullable', 'string'],

            'tags' => ['nullable', 'array'],
            'tags.existing' => ['nullable', 'array'],
            'tags.existing.*' => ['integer', 'exists:taxonomy_terms,id'],
            'tags.new' => ['nullable', 'array'],
            'tags.new.*' => ['string', 'max:255'],

            'category_ids' => ['nullable', 'array'],

            'image_gallery_items' => ['nullable', 'array'],
            'image_gallery_items.*' => ['array'],

            'image_gallery_items.*.id' => ['nullable', 'integer'],
            'image_gallery_items.*.title' => ['nullable', 'string', 'max:255'],
            'image_gallery_items.*.image.id' => ['nullable', 'exists:media_files,id'],
            'image_gallery_items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'video_gallery_items' => ['nullable', 'array'],
            'video_gallery_items.*' => ['array'],
            'video_gallery_items.*.id' => ['nullable', 'integer'],
            'video_gallery_items.*.title' => ['nullable', 'string', 'max:255'],
            'video_gallery_items.*.video_url' => ['nullable', 'url:http,https', 'max:2048'],
            'video_gallery_items.*.cover' => ['nullable', 'array'],
            'video_gallery_items.*.cover.id' => ['nullable', 'integer', 'exists:media_files,id'],
            'video_gallery_items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'requirements' => ['nullable', 'array'],
            'requirements.*' => ['array'],

            'installation_step_body' => ['nullable', 'string'],

            'faqs' => ['nullable', 'array'],
            'faqs.*' => ['array'],

            'releases' => ['nullable', 'array'],
            'releases.*' => ['array'],

            'attributes' => ['nullable', 'array'],
            'attributes.*.id' => ['nullable', 'integer', 'exists:product_attribute_assignments,id'],
            'attributes.*.attribute_id' => ['required', 'integer', 'exists:product_attributes,id'],
            'attributes.*.value_ids' => ['nullable', 'array'],
            'attributes.*.value_ids.*' => ['integer', 'exists:product_attribute_values,id'],
            'attributes.*.is_visible' => ['nullable', 'boolean'],
            'attributes.*.is_variation_defining' => ['nullable', 'boolean'],
            'attributes.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'custom_attributes' => ['nullable', 'array'],
            'custom_attributes.*.id' => ['nullable', 'integer', 'exists:product_custom_attributes,id'],
            'custom_attributes.*.title' => ['nullable', 'string', 'max:255'],
            'custom_attributes.*.is_visible' => ['nullable', 'boolean'],
            'custom_attributes.*.is_variation_defining' => ['nullable', 'boolean'],
            'custom_attributes.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'custom_attributes.*.values' => ['nullable', 'array'],
            'custom_attributes.*.values.*.id' => ['nullable', 'integer', 'exists:product_custom_attribute_values,id'],
            'custom_attributes.*.values.*.title' => ['nullable', 'string', 'max:255'],
            'custom_attributes.*.values.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'sale_items' => ['nullable', 'array'],
            'sale_items.*.id' => ['nullable', 'integer', 'exists:product_sale_items,id'],
            'sale_items.*.sku' => ['nullable', 'string', 'max:255'],
            'sale_items.*.title' => ['nullable', 'string', 'max:255'],
            'sale_items.*.edition_key' => ['nullable', 'string', 'max:80'],
            'sale_items.*.version' => ['nullable', 'string', 'max:50'],
            'sale_items.*.base_price' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.status' => [
                'nullable',
                Rule::in(array_column(ProductSaleItemStatusEnum::cases(), 'value')),
            ],
            'sale_items.*.vip_access_enabled' => ['nullable', 'boolean'],
            'sale_items.*.download_limit' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.download_expires_in_days' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.max_quantity_per_order' => ['nullable', 'integer', 'min:1'],
            'sale_items.*.description' => ['nullable', 'string'],
            'sale_items.*.launcher_enabled' => ['nullable', 'boolean'],
            'sale_items.*.launcher_free_claim_enabled' => ['nullable', 'boolean'],
            'sale_items.*.launcher_title' => ['nullable', 'string', 'max:255'],
            'sale_items.*.launcher_excerpt' => ['nullable', 'string', 'max:2000'],
            'sale_items.*.launcher_cover_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'sale_items.*.launcher_sort_order' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.launcher_support_enabled' => ['nullable', 'boolean'],
            'sale_items.*.launcher_support_url' => ['nullable', 'string', 'url:https', 'max:2048'],
            'sale_items.*.launcher_usage_guide_enabled' => ['nullable', 'boolean'],
            'sale_items.*.launcher_usage_guide_content' => ['nullable', 'string', 'max:20000'],

            /*
            |--------------------------------------------------------------------------
            | Launcher manifest
            |--------------------------------------------------------------------------
            */
            'sale_items.*.manifest' => ['nullable', 'array'],
            'sale_items.*.manifest.download_type' => [
                'nullable',
                Rule::in(['single_file', 'multiple_files', 'split_archive']),
            ],
            'sale_items.*.manifest.archive_format' => [
                'nullable',
                Rule::in(['7z']),
            ],
            'sale_items.*.manifest.archive_password' => ['nullable', 'string', 'max:512'],
            'sale_items.*.manifest.assembled_file_name' => [
                'nullable', 'string', 'max:255',
                'not_regex:/[\\\\\/]/',
                'not_in:.,..',
            ],
            'sale_items.*.manifest.install_directory' => ['nullable', 'string', 'max:500'],
            'sale_items.*.manifest.install_folder_name' => [
                'nullable', 'string', 'max:150',
                'not_regex:/[\\\\\/:*?"<>|]/',
                'not_in:.,..',
            ],
            'sale_items.*.manifest.requires_administrator' => ['nullable', 'boolean'],
            'sale_items.*.manifest.has_option_file' => ['nullable', 'boolean'],
            'sale_items.*.manifest.main_executable_path' => ['nullable', 'string', 'max:500'],
            'sale_items.*.manifest.working_directory' => ['nullable', 'string', 'max:500'],
            'sale_items.*.manifest.launch_arguments' => ['nullable', 'string', 'max:2000'],
            'sale_items.*.manifest.requires_sider' => ['nullable', 'boolean'],
            'sale_items.*.manifest.sider_executable_path' => ['nullable', 'string', 'max:500'],
            'sale_items.*.manifest.sider_working_directory' => ['nullable', 'string', 'max:500'],
            'sale_items.*.manifest.sider_arguments' => ['nullable', 'string', 'max:2000'],
            'sale_items.*.manifest.run_sider_as_administrator' => ['nullable', 'boolean'],

            /*
            |--------------------------------------------------------------------------
            | DLL injection launch (e.g. a FIFA-style live editor, in place of Sider)
            |--------------------------------------------------------------------------
            */
            'sale_items.*.manifest.requires_injection' => ['nullable', 'boolean'],
            'sale_items.*.manifest.inject_dll_path' => ['nullable', 'string', 'max:500'],

            /*
            |--------------------------------------------------------------------------
            | Content key (encrypted database protection)
            |--------------------------------------------------------------------------
            | content_key_id is an opaque, versioned identifier (e.g. "pgl6-db-2026-01"),
            | never the product slug -- shipping a re-encrypted database update under a
            | new id lets the launcher tell an old cached key apart from a new one.
            | Cross-field checks (both-or-neither, exact 32-byte key, uniqueness across
            | ALL manifests) live in withValidator() below, matching how this file
            | already handles sider_executable_path and the CPU compatibility fields.
            */
            'sale_items.*.manifest.content_key_id' => [
                'nullable', 'string', 'max:128',
                'regex:/^[A-Za-z0-9._-]+$/',
            ],
            'sale_items.*.manifest.content_key_value' => ['nullable', 'string', 'max:512'],

            /*
            |--------------------------------------------------------------------------
            | Content protection policy (delivered to sider.dll at launch)
            |--------------------------------------------------------------------------
            | Paths and module digests, not secrets: every customer already has these
            | files on disk. They live here rather than compiled into pgldrm.cpp so that
            | protecting a new file or updating a Lua module is an admin-panel edit
            | instead of a sider.dll rebuild plus a launcher release.
            |
            | No value may contain a line break. The policy is serialized for sider in a
            | line-oriented format (one directive per line, no escaping by design), so an
            | embedded newline would let a single field inject additional directives --
            | for instance an extra MODULE line granting content tier to a module of the
            | submitter's choosing. The launcher drops such values defensively too, but
            | they must never reach the database in the first place.
            |
            | The three path lists arrive from the admin form as one-path-per-line
            | text, but an API or artisan caller may pass a real array -- both are
            | accepted, and SyncProductSaleItemManifestAction normalizes either into
            | a clean list (trimming, dropping blanks and duplicates, and rejecting
            | any value with an embedded line break a second time).
            */
            'sale_items.*.manifest.protected_folder_markers' => ['nullable'],
            'sale_items.*.manifest.protected_exact_paths' => ['nullable'],
            'sale_items.*.manifest.protected_excluded_paths' => ['nullable'],
            // One "<offset> <length> <path>" per line, at most 32 lines; length 1..64 so a
            // typo cannot overwrite a large part of a file in memory.
            'sale_items.*.manifest.protected_watermark_sites' => [
                'nullable', 'string', 'max:10000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', (string)$value) ?: [])));
                    if (count($lines) > 32) {
                        $fail('حداکثر ۳۲ محل واترمارک مجاز است.');
                        return;
                    }
                    foreach ($lines as $line) {
                        if (!preg_match('/\A\d{1,12} ([1-9]|[1-5]\d|6[0-4]) \S.{0,250}\z/', $line)) {
                            $fail("خط واترمارک نامعتبر است: «{$line}» — قالب درست: آفست طول مسیر (طول ۱ تا ۶۴).");
                            return;
                        }
                    }
                },
            ],

            // Posted by the form as a JSON string (one field instead of ~150 hidden
            // inputs for a 50-module list). Structure is validated during
            // normalization, where a malformed entry can be dropped individually.
            'sale_items.*.manifest.protected_modules_json' => ['nullable', 'string', 'max:500000'],
            'sale_items.*.manifest.protected_modules' => ['nullable', 'array', 'max:1000'],
            'sale_items.*.manifest.protected_modules.*.path' => [
                'required', 'string', 'max:512', 'not_regex:/[\r\n]/',
            ],
            'sale_items.*.manifest.protected_modules.*.tier' => [
                'required', Rule::in(['content', 'shipped']),
            ],
            'sale_items.*.manifest.protected_modules.*.sha256' => [
                'required', 'regex:/\A[a-fA-F0-9]{64}\z/',
            ],
            'sale_items.*.manifest.protected_strict_modules' => ['nullable', 'boolean'],
            'sale_items.*.manifest.protected_enforce_native_dlls' => ['nullable', 'boolean'],

            /*
            |--------------------------------------------------------------------------
            | CPU compatibility
            |--------------------------------------------------------------------------
            */
            'sale_items.*.manifest.cpu_compatibility' => ['nullable', 'array'],
            'sale_items.*.manifest.cpu_compatibility.enabled' => ['nullable', 'boolean'],
            'sale_items.*.manifest.cpu_compatibility.default_mode' => [
                'nullable',
                Rule::in(ProductSaleItemManifest::CPU_COMPATIBILITY_MODES),
            ],
            'sale_items.*.manifest.cpu_compatibility.apply_to_game' => ['nullable', 'boolean'],
            'sale_items.*.manifest.cpu_compatibility.apply_to_sider' => ['nullable', 'boolean'],
            'sale_items.*.manifest.cpu_compatibility.allow_user_override' => ['nullable', 'boolean'],

            /*
            |--------------------------------------------------------------------------
            | Manifest capabilities
            |--------------------------------------------------------------------------
            */
            'sale_items.*.manifest.capabilities' => ['nullable', 'array'],
            'sale_items.*.manifest.capabilities.supports_install' => ['nullable', 'boolean'],
            'sale_items.*.manifest.capabilities.supports_launch' => ['nullable', 'boolean'],
            'sale_items.*.manifest.capabilities.supports_sider' => ['nullable', 'boolean'],
            'sale_items.*.manifest.capabilities.supports_injection' => ['nullable', 'boolean'],
            'sale_items.*.manifest.capabilities.supports_settings' => ['nullable', 'boolean'],
            'sale_items.*.manifest.capabilities.supports_profiles' => ['nullable', 'boolean'],
            'sale_items.*.manifest.capabilities.supports_update' => ['nullable', 'boolean'],
            'sale_items.*.manifest.capabilities.supports_mod_manager' => ['nullable', 'boolean'],

            /*
            |--------------------------------------------------------------------------
            | Manifest install options
            |--------------------------------------------------------------------------
            */
            'sale_items.*.manifest.install_options' => ['nullable', 'array'],
            'sale_items.*.manifest.install_options.extract_enabled' => ['nullable', 'boolean'],
            'sale_items.*.manifest.install_options.delete_archives_after_install' => ['nullable', 'boolean'],
            'sale_items.*.manifest.install_options.ask_before_delete_archives' => ['nullable', 'boolean'],
            'sale_items.*.manifest.install_options.clean_temp_after_install' => ['nullable', 'boolean'],
            'sale_items.*.manifest.install_options.create_desktop_shortcut' => ['nullable', 'boolean'],
            'sale_items.*.manifest.install_options.required_install_space_bytes' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.manifest.install_options.estimated_installed_size_bytes' => ['nullable', 'integer', 'min:0'],

            'sale_items.*.discount' => ['nullable', 'array'],
            'sale_items.*.discount.id' => ['nullable', 'integer'],
            'sale_items.*.discount.discounted_price' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.discount.starts_at' => ['nullable', 'date'],
            'sale_items.*.discount.ends_at' => ['nullable', 'date'],
            'sale_items.*.discount.show_special_box' => ['nullable', 'boolean'],
            'sale_items.*.discount.special_box_title' => ['nullable', 'string', 'max:255'],
            'sale_items.*.discount.special_box_description' => ['nullable', 'string', 'max:1000'],

            'sale_items.*.attribute_values' => ['nullable', 'array'],
            'sale_items.*.attribute_values.*' => ['nullable', 'string', 'max:255'],

            'sale_items.*.files' => ['nullable', 'array'],
            'sale_items.*.files.*.id' => ['nullable', 'integer', 'exists:product_sale_item_files,id'],
            'sale_items.*.files.*.title' => ['nullable', 'string', 'max:255'],
            'sale_items.*.files.*.url' => ['nullable', 'url', 'max:2048'],
            'sale_items.*.files.*.file_name' => ['nullable', 'string', 'max:255', 'not_regex:/[\\\\\/]/'],
            'sale_items.*.files.*.size_bytes' => ['nullable', 'integer', 'min:1'],
            'sale_items.*.files.*.sha256' => ['nullable', 'regex:/^[a-fA-F0-9]{64}$/'],
            // Base64 RSA-PSS signature over the sha256 above, produced by the release
            // tool. Length is not pinned: a bigger signing key produces a longer
            // signature, and the launcher rejects a malformed one anyway.
            'sale_items.*.files.*.sha256_signature' => ['nullable', 'string', 'max:2048'],
            // IDM mirror on a separate host. Without a rule here validated() would drop it
            // silently on save (the same trap that ate content_key_id in 2026-08).
            'sale_items.*.files.*.mirror_url' => ['nullable', 'url:https,http', 'max:2048'],
            'sale_items.*.files.*.signature_key_id' => ['nullable', 'regex:/^[a-fA-F0-9]{64}$/'],
            'sale_items.*.files.*.mime_type' => ['nullable', 'string', 'max:255'],
            'sale_items.*.files.*.part_number' => ['nullable', 'integer', 'min:1'],
            'sale_items.*.files.*.archive_group' => ['nullable', 'string', 'max:255'],
            'sale_items.*.files.*.is_required' => ['nullable', 'boolean'],
            'sale_items.*.files.*.is_active' => ['nullable', 'boolean'],
            'sale_items.*.files.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'sale_items.*.launcher_updates' => ['nullable', 'array'],
            'sale_items.*.launcher_updates.*.id' => ['nullable', 'integer'],
            'sale_items.*.launcher_updates.*.version' => ['required_with:sale_items.*.launcher_updates', 'string', 'max:50'],
            'sale_items.*.launcher_updates.*.title' => ['required_with:sale_items.*.launcher_updates', 'string', 'max:255'],
            'sale_items.*.launcher_updates.*.description' => ['nullable', 'string'],
            'sale_items.*.launcher_updates.*.changelog' => ['nullable', 'string'],
            'sale_items.*.launcher_updates.*.archive_password' => ['nullable', 'string', 'max:512'],
            'sale_items.*.launcher_updates.*.is_enabled' => ['nullable', 'boolean'],
            'sale_items.*.launcher_updates.*.is_mandatory' => ['nullable', 'boolean'],
            'sale_items.*.launcher_updates.*.has_option_file' => ['nullable', 'boolean'],
            'sale_items.*.launcher_updates.*.published_at' => ['nullable', 'date'],
            'sale_items.*.launcher_updates.*.files' => ['nullable', 'array'],
            'sale_items.*.launcher_updates.*.files.*.id' => ['nullable', 'integer'],
            'sale_items.*.launcher_updates.*.files.*.title' => ['nullable', 'string', 'max:255'],
            'sale_items.*.launcher_updates.*.files.*.file_name' => ['required_with:sale_items.*.launcher_updates.*.files', 'string', 'max:255', 'not_regex:/[\\\\\/]/', 'regex:/\.7z(?:\.\d{3})?$/i'],
            'sale_items.*.launcher_updates.*.files.*.url' => ['required_with:sale_items.*.launcher_updates.*.files', 'url:https', 'max:2048'],
            'sale_items.*.launcher_updates.*.files.*.size_bytes' => ['required_with:sale_items.*.launcher_updates.*.files', 'integer', 'min:1'],
            'sale_items.*.launcher_updates.*.files.*.sha256' => ['required_with:sale_items.*.launcher_updates.*.files', 'regex:/^[a-fA-F0-9]{64}$/'],
            'sale_items.*.launcher_updates.*.files.*.mime_type' => ['nullable', 'string', 'max:255'],
            'sale_items.*.launcher_updates.*.files.*.part_number' => ['nullable', 'integer', 'min:1'],
            'sale_items.*.launcher_updates.*.files.*.is_required' => ['nullable', 'boolean'],
            'sale_items.*.launcher_updates.*.files.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'sale_items.*.launcher_mod_groups' => ['nullable', 'array'],
            'sale_items.*.launcher_mod_groups.*.id' => ['nullable', 'integer'],
            'sale_items.*.launcher_mod_groups.*.key' => ['required_with:sale_items.*.launcher_mod_groups', 'alpha_dash', 'max:100'],
            'sale_items.*.launcher_mod_groups.*.title' => ['required_with:sale_items.*.launcher_mod_groups', 'string', 'max:255'],
            'sale_items.*.launcher_mod_groups.*.is_exclusive' => ['nullable', 'boolean'],

            'sale_items.*.launcher_mods' => ['nullable', 'array'],
            'sale_items.*.launcher_mods.*.id' => ['nullable', 'integer'],
            'sale_items.*.launcher_mods.*.name' => ['required_with:sale_items.*.launcher_mods', 'string', 'max:255'],
            'sale_items.*.launcher_mods.*.slug' => ['required_with:sale_items.*.launcher_mods', 'alpha_dash', 'max:255'],
            'sale_items.*.launcher_mods.*.version' => ['required_with:sale_items.*.launcher_mods', 'string', 'max:50'],
            'sale_items.*.launcher_mods.*.install_target' => ['required_with:sale_items.*.launcher_mods', Rule::enum(LauncherModInstallTargetEnum::class)],
            'sale_items.*.launcher_mods.*.install_mode' => ['nullable', 'in:toggle,permanent'],
            'sale_items.*.launcher_mods.*.group_key' => ['nullable', 'alpha_dash', 'max:100'],
            'sale_items.*.launcher_mods.*.short_description' => ['nullable', 'string', 'max:2000'],
            'sale_items.*.launcher_mods.*.description' => ['nullable', 'string'],
            'sale_items.*.launcher_mods.*.archive_password' => ['nullable', 'string', 'max:512'],
            'sale_items.*.launcher_mods.*.image_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'sale_items.*.launcher_mods.*.priority' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.launcher_mods.*.is_enabled' => ['nullable', 'boolean'],
            'sale_items.*.launcher_mods.*.is_required' => ['nullable', 'boolean'],
            'sale_items.*.launcher_mods.*.compatible_base_versions' => ['nullable', 'array'],
            'sale_items.*.launcher_mods.*.compatible_base_versions.*' => ['string', 'max:50'],
            'sale_items.*.launcher_mods.*.compatible_update_versions' => ['nullable', 'array'],
            'sale_items.*.launcher_mods.*.compatible_update_versions.*' => ['string', 'max:50'],
            'sale_items.*.launcher_mods.*.compatible_base_versions_text' => ['nullable', 'string', 'max:1000'],
            'sale_items.*.launcher_mods.*.compatible_update_versions_text' => ['nullable', 'string', 'max:1000'],
            'sale_items.*.launcher_mods.*.published_at' => ['nullable', 'date'],
            'sale_items.*.launcher_mods.*.files' => ['nullable', 'array'],
            'sale_items.*.launcher_mods.*.files.*.id' => ['nullable', 'integer'],
            'sale_items.*.launcher_mods.*.files.*.title' => ['nullable', 'string', 'max:255'],
            'sale_items.*.launcher_mods.*.files.*.file_name' => ['required_with:sale_items.*.launcher_mods.*.files', 'string', 'max:255', 'not_regex:/[\\\\\/]/', 'regex:/\.7z(?:\.\d{3})?$/i'],
            'sale_items.*.launcher_mods.*.files.*.url' => ['required_with:sale_items.*.launcher_mods.*.files', 'url:https', 'max:2048'],
            'sale_items.*.launcher_mods.*.files.*.size_bytes' => ['required_with:sale_items.*.launcher_mods.*.files', 'integer', 'min:1'],
            'sale_items.*.launcher_mods.*.files.*.sha256' => ['required_with:sale_items.*.launcher_mods.*.files', 'regex:/^[a-fA-F0-9]{64}$/'],
            'sale_items.*.launcher_mods.*.files.*.mime_type' => ['nullable', 'string', 'max:255'],
            'sale_items.*.launcher_mods.*.files.*.part_number' => ['nullable', 'integer', 'min:1'],
            'sale_items.*.launcher_mods.*.files.*.is_required' => ['nullable', 'boolean'],
            'sale_items.*.launcher_mods.*.files.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'addons' => ['nullable', 'array'],

            'addons.*.id' => ['nullable', 'integer', 'exists:product_addons,id'],
            'addons.*.title' => ['required', 'string', 'max:255'],
            'addons.*.description' => ['nullable', 'string'],
            'addons.*.price' => ['required', 'integer', 'min:0'],
            'addons.*.seller_commission_percent' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,2'],
            'addons.*.is_active' => ['nullable', 'boolean'],
            'addons.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'addons.*.metadata' => ['nullable', 'array'],

            'information_fields' => ['nullable', 'array'],
            'information_fields.*' => ['nullable', 'string', 'max:255'],

            'seo' => ['nullable', 'array'],

            'display_setting' => ['nullable', 'array'],

            'team_members' => ['nullable', 'array'],

            'update_progress' => ['nullable', 'array'],
            'update_progress.is_visible' => ['nullable', 'boolean'],
            'update_progress.title' => ['nullable', 'string', 'max:255'],
            'update_progress.description' => ['nullable', 'string', 'max:1000'],
            'update_progress.progress_percentage' => ['nullable', 'integer'],
            'update_progress.started_at' => ['nullable', 'date'],
            'update_progress.release_at' => ['nullable', 'date'],

            'action_links' => ['nullable', 'array'],
            'action_links.*' => ['array'],
            'action_links.*.id' => ['nullable', 'integer'],
            'action_links.*.title' => ['nullable', 'string', 'max:120'],
            'action_links.*.url' => ['nullable', 'string', 'max:2048'],
            'action_links.*.open_in_new_tab' => ['nullable', 'boolean'],

            'medal_assignments' => ['nullable', 'array'],
            'medal_assignments.*' => ['array'],
            'medal_assignments.*.id' => ['nullable', 'integer', Rule::exists('product_medal_assignments', 'id')],
            'medal_assignments.*.product_medal_id' => ['required', 'integer', 'distinct', Rule::exists('product_medals', 'id')],
        ];
    }

    /**
     * Launcher publication is intentionally stricter than normal website
     * products: the Windows client must never receive an incomplete package.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            foreach ((array)$this->input('sale_items', []) as $index => $item) {
                $prefix = "sale_items.{$index}";

                if (filter_var($item['vip_access_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)
                    && filter_var($item['launcher_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    $validator->errors()->add(
                        "{$prefix}.vip_access_enabled",
                        'دسترسی VIP این بخش فقط برای فایل‌های دانلود مستقیم قابل فعال‌سازی است.'
                    );
                }

                if (
                    filter_var(
                        $item['launcher_support_enabled'] ?? false,
                        FILTER_VALIDATE_BOOLEAN
                    )
                    && blank($item['launcher_support_url'] ?? null)
                ) {
                    $validator->errors()->add(
                        "{$prefix}.launcher_support_url",
                        'آدرس صفحه پشتیبانی هنگام فعال بودن پشتیبانی آنلاین الزامی است.'
                    );
                }

                if (
                    filter_var(
                        $item['launcher_usage_guide_enabled'] ?? false,
                        FILTER_VALIDATE_BOOLEAN
                    )
                    && blank($item['launcher_usage_guide_content'] ?? null)
                ) {
                    $validator->errors()->add(
                        "{$prefix}.launcher_usage_guide_content",
                        'متن آموزش کاربردی هنگام فعال بودن این گزینه الزامی است.'
                    );
                }

                if (!filter_var(
                    $item['launcher_enabled'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                )) {
                    continue;
                }

                if (!filter_var($item['launcher_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    continue;
                }

                $prefix = "sale_items.{$index}";
                $manifest = (array)($item['manifest'] ?? []);
                $files = array_values(array_filter(
                    (array)($item['files'] ?? []),
                    static fn(mixed $file): bool => is_array($file)
                        && filter_var($file['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN)
                ));

                if (blank($item['launcher_title'] ?? null)) {
                    $validator->errors()->add(
                        "{$prefix}.launcher_title",
                        'عنوان محصول در لانچر الزامی است.'
                    );
                }

                if (blank($item['version'] ?? null)) {
                    $validator->errors()->add(
                        "{$prefix}.version",
                        'نسخه محصول برای انتشار در لانچر الزامی است.'
                    );
                }

                if (blank($manifest['install_folder_name'] ?? null)) {
                    $validator->errors()->add(
                        "{$prefix}.manifest.install_folder_name",
                        'نام پوشه نصب لانچر الزامی است.'
                    );
                }

                if ($files === []) {
                    $validator->errors()->add(
                        "{$prefix}.files",
                        'حداقل یک فایل فعال 7z برای لانچر وارد کنید.'
                    );
                }

                foreach ($files as $fileIndex => $file) {
                    foreach (['file_name', 'url', 'size_bytes', 'sha256'] as $field) {
                        if (blank($file[$field] ?? null)) {
                            $validator->errors()->add(
                                "{$prefix}.files.{$fileIndex}.{$field}",
                                'این مشخصه فایل برای انتشار در لانچر الزامی است.'
                            );
                        }
                    }

                    if (filled($file['file_name'] ?? null)
                        && preg_match('/\.7z(?:\.\d{3})?$/i', (string)$file['file_name']) !== 1) {
                        $validator->errors()->add(
                            "{$prefix}.files.{$fileIndex}.file_name",
                            'نام فایل باید با .7z یا شماره پارت مانند .7z.001 پایان یابد.'
                        );
                    }

                    if (filled($file['file_name'] ?? null)
                        && basename(str_replace('\\', '/', (string)$file['file_name']))
                        !== (string)$file['file_name']) {
                        $validator->errors()->add(
                            "{$prefix}.files.{$fileIndex}.file_name",
                            'نام فایل نباید شامل مسیر پوشه باشد.'
                        );
                    }

                    if (filled($file['url'] ?? null)
                        && strtolower((string)parse_url((string)$file['url'], PHP_URL_SCHEME)) !== 'https') {
                        $validator->errors()->add(
                            "{$prefix}.files.{$fileIndex}.url",
                            'URL فایل لانچر باید HTTPS باشد.'
                        );
                    }

                    if (filled($file['file_name'] ?? null) && filled($file['url'] ?? null)) {
                        $configuredFileName = basename(str_replace(
                            '\\',
                            '/',
                            trim((string)$file['file_name'])
                        ));
                        $sourceFileName = basename(str_replace(
                            '\\',
                            '/',
                            rawurldecode((string)parse_url((string)$file['url'], PHP_URL_PATH))
                        ));

                        if ($sourceFileName === '' || !hash_equals($sourceFileName, $configuredFileName)) {
                            $validator->errors()->add(
                                "{$prefix}.files.{$fileIndex}.file_name",
                                'نام فایل باید دقیقاً با نام واقعی فایل در انتهای URL دانلود یکسان باشد.'
                            );
                        }
                    }
                }

                if ($this->isSplitArchivePayload($manifest, $files) && $files !== []) {
                    $partNumbers = [];
                    $multipartBaseName = null;

                    foreach ($files as $fileIndex => $file) {
                        $partNumber = filter_var(
                            $file['part_number'] ?? null,
                            FILTER_VALIDATE_INT,
                            ['options' => ['min_range' => 1]]
                        );

                        if ($partNumber === false) {
                            $validator->errors()->add(
                                "{$prefix}.files.{$fileIndex}.part_number",
                                'شماره پارت برای آرشیو چندقسمتی الزامی است و باید از ۱ شروع شود.'
                            );
                        } else {
                            $partNumbers[] = (int)$partNumber;
                        }

                        $fileName = basename(str_replace(
                            '\\',
                            '/',
                            trim((string)($file['file_name'] ?? ''))
                        ));
                        $matches = [];

                        if (preg_match('/^(.*\.7z)\.(\d{3,})$/i', $fileName, $matches) !== 1) {
                            $validator->errors()->add(
                                "{$prefix}.files.{$fileIndex}.file_name",
                                'در حالت چندپارتی نام همه فایل‌ها باید مانند package.7z.001 باشد.'
                            );
                        } else {
                            $baseName = strtolower($matches[1]);
                            $suffixPartNumber = (int)$matches[2];
                            $multipartBaseName ??= $baseName;

                            if ($multipartBaseName !== $baseName) {
                                $validator->errors()->add(
                                    "{$prefix}.files.{$fileIndex}.file_name",
                                    'تمام پارت‌ها باید نام پایه یکسان داشته باشند.'
                                );
                            }

                            if ($partNumber !== false && $suffixPartNumber !== (int)$partNumber) {
                                $validator->errors()->add(
                                    "{$prefix}.files.{$fileIndex}.part_number",
                                    'شماره پارت باید با شماره انتهای نام فایل یکسان باشد.'
                                );
                            }
                        }

                        if (!filter_var(
                            $file['is_required'] ?? true,
                            FILTER_VALIDATE_BOOLEAN
                        )) {
                            $validator->errors()->add(
                                "{$prefix}.files.{$fileIndex}.is_required",
                                'تمام پارت‌های آرشیو چندقسمتی برای نصب ضروری هستند.'
                            );
                        }
                    }

                    if (count($partNumbers) === count($files)) {
                        sort($partNumbers, SORT_NUMERIC);
                        $expectedPartNumbers = range(1, count($files));

                        if ($partNumbers !== $expectedPartNumbers) {
                            $validator->errors()->add(
                                "{$prefix}.files",
                                'شماره پارت‌ها باید بدون تکرار و به‌صورت پیوسته از ۱ تا تعداد فایل‌ها باشند.'
                            );
                        }
                    }
                }

                $hasOptionFile = filter_var(
                    data_get($manifest, 'has_option_file', false),
                    FILTER_VALIDATE_BOOLEAN
                );

                if ($hasOptionFile) {
                    $supportsInstall = filter_var(
                        data_get($manifest, 'capabilities.supports_install', true),
                        FILTER_VALIDATE_BOOLEAN
                    );

                    $extractEnabled = filter_var(
                        data_get($manifest, 'install_options.extract_enabled', true),
                        FILTER_VALIDATE_BOOLEAN
                    );

                    if (!$supportsInstall) {
                        $validator->errors()->add(
                            "{$prefix}.manifest.has_option_file",
                            'برای استفاده از OPTION FILE باید قابلیت نصب محصول فعال باشد.'
                        );
                    }

                    if (!$extractEnabled) {
                        $validator->errors()->add(
                            "{$prefix}.manifest.install_options.extract_enabled",
                            'برای استفاده از OPTION FILE باید استخراج فایل‌ها فعال باشد.'
                        );
                    }

                    if ($files === []) {
                        $validator->errors()->add(
                            "{$prefix}.files",
                            'وقتی OPTION FILE فعال است، بسته پایه باید حداقل یک فایل 7z داشته باشد.'
                        );
                    }
                }

                if (filter_var(
                        data_get($manifest, 'capabilities.supports_launch', true),
                        FILTER_VALIDATE_BOOLEAN
                    ) && blank($manifest['main_executable_path'] ?? null)) {
                    $validator->errors()->add(
                        "{$prefix}.manifest.main_executable_path",
                        'مسیر فایل اجرایی محصول الزامی است.'
                    );
                }

                $requiresSider = filter_var(
                    $manifest['requires_sider'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                $supportsSider = filter_var(
                        data_get($manifest, 'capabilities.supports_sider', $requiresSider),
                        FILTER_VALIDATE_BOOLEAN
                    ) || $requiresSider;

                if ($supportsSider && blank($manifest['sider_executable_path'] ?? null)) {
                    $validator->errors()->add(
                        "{$prefix}.manifest.sider_executable_path",
                        'وقتی Sider برای این نسخه فعال است، مسیر فایل اجرایی آن الزامی است.'
                    );
                }

                if ($supportsSider && blank($manifest['sider_working_directory'] ?? null)) {
                    $validator->errors()->add(
                        "{$prefix}.manifest.sider_working_directory",
                        'وقتی Sider برای این نسخه فعال است، پوشه اجرای آن الزامی است.'
                    );
                }

                $requiresInjection = filter_var(
                    $manifest['requires_injection'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                $supportsInjection = filter_var(
                        data_get($manifest, 'capabilities.supports_injection', $requiresInjection),
                        FILTER_VALIDATE_BOOLEAN
                    ) || $requiresInjection;

                if ($supportsInjection && blank($manifest['inject_dll_path'] ?? null)) {
                    $validator->errors()->add(
                        "{$prefix}.manifest.inject_dll_path",
                        'وقتی تزریق DLL برای این نسخه فعال است، مسیر فایل DLL الزامی است.'
                    );
                }

                $this->validateContentKey(
                    validator: $validator,
                    manifest: $manifest,
                    prefix: $prefix,
                    currentSaleItemId: $item['id'] ?? null,
                    supportsSider: $supportsSider,
                );

                $cpuCompatibilityEnabled = filter_var(
                    data_get($manifest, 'cpu_compatibility.enabled', false),
                    FILTER_VALIDATE_BOOLEAN
                );

                $applyCpuToGame = filter_var(
                    data_get($manifest, 'cpu_compatibility.apply_to_game', false),
                    FILTER_VALIDATE_BOOLEAN
                );

                $applyCpuToSider = filter_var(
                    data_get($manifest, 'cpu_compatibility.apply_to_sider', false),
                    FILTER_VALIDATE_BOOLEAN
                );

                $cpuCompatibilityMode = strtolower(trim((string) data_get(
                    $manifest,
                    'cpu_compatibility.default_mode',
                    'off'
                )));

                $allowCpuUserOverride = filter_var(
                    data_get($manifest, 'cpu_compatibility.allow_user_override', false),
                    FILTER_VALIDATE_BOOLEAN
                );

                if ($cpuCompatibilityEnabled && !$applyCpuToGame && !$applyCpuToSider) {
                    $validator->errors()->add(
                        "{$prefix}.manifest.cpu_compatibility.enabled",
                        'برای CPU Compatibility حداقل یکی از مقصدهای بازی یا Sider باید فعال باشد.'
                    );
                }

                if ($cpuCompatibilityEnabled && $applyCpuToSider && !$supportsSider) {
                    $validator->errors()->add(
                        "{$prefix}.manifest.cpu_compatibility.apply_to_sider",
                        'برای اعمال CPU Compatibility روی Sider باید پشتیبانی از Sider فعال باشد.'
                    );
                }

                if ($cpuCompatibilityEnabled && $cpuCompatibilityMode === 'manual' && !$allowCpuUserOverride) {
                    $validator->errors()->add(
                        "{$prefix}.manifest.cpu_compatibility.allow_user_override",
                        'در حالت دستی CPU Compatibility باید اجازه تغییر توسط کاربر فعال باشد.'
                    );
                }

                foreach ([
                             'main_executable_path',
                             'working_directory',
                             'sider_executable_path',
                             'sider_working_directory',
                             'inject_dll_path',
                         ] as $pathField) {
                    $path = $manifest[$pathField] ?? null;

                    if (filled($path) && !$this->isSafeRelativeWindowsPath((string)$path)) {
                        $validator->errors()->add(
                            "{$prefix}.manifest.{$pathField}",
                            'مسیر باید نسبی و داخل پوشه محصول باشد.'
                        );
                    }
                }

                $this->validateLauncherModConfiguration($validator, $item, $prefix);

                foreach (['launcher_updates' => 'آپدیت', 'launcher_mods' => 'ماد'] as $collection => $label) {
                    foreach ((array)($item[$collection] ?? []) as $contentIndex => $content) {
                        if (!is_array($content)) {
                            continue;
                        }

                        $contentFiles = (array)($content['files'] ?? []);

                        if (filter_var($content['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)
                            && $contentFiles === []) {
                            $validator->errors()->add(
                                "{$prefix}.{$collection}.{$contentIndex}.files",
                                "برای {$label} فعال حداقل یک فایل 7z لازم است."
                            );
                        }

                        if ($collection === 'launcher_updates'
                            && filter_var($content['has_option_file'] ?? false, FILTER_VALIDATE_BOOLEAN)
                            && $contentFiles === []) {
                            $validator->errors()->add(
                                "{$prefix}.launcher_updates.{$contentIndex}.files",
                                'وقتی OPTION FILE برای آپدیت فعال است، بسته آپدیت باید حداقل یک فایل 7z داشته باشد.'
                            );
                        }

                        if ($collection === 'launcher_mods'
                            && filter_var($content['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                            $imageMediaId = filled($content['image_media_id'] ?? null)
                                ? (int)$content['image_media_id']
                                : null;

                            if ($imageMediaId === null) {
                                $validator->errors()->add(
                                    "{$prefix}.launcher_mods.{$contentIndex}.image_media_id",
                                    'برای ماد منتشرشده انتخاب تصویر مستطیلی الزامی است.'
                                );
                            } else {
                                $image = MediaFile::query()
                                    ->with('primaryVariant')
                                    ->find($imageMediaId);
                                $variant = $image?->primaryVariant;

                                if (
                                    $image === null
                                    || $image->url === null
                                    || $variant === null
                                    || (int)$variant->width <= 0
                                    || (int)$variant->height <= 0
                                ) {
                                    $validator->errors()->add(
                                        "{$prefix}.launcher_mods.{$contentIndex}.image_media_id",
                                        'تصویر ماد باید عمومی، پردازش‌شده و دارای ابعاد معتبر باشد.'
                                    );
                                } elseif ((int)$variant->width <= (int)$variant->height) {
                                    $validator->errors()->add(
                                        "{$prefix}.launcher_mods.{$contentIndex}.image_media_id",
                                        'تصویر ماد باید مستطیلی افقی باشد (عرض بیشتر از ارتفاع).'
                                    );
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Validate Mod Manager group rules and version compatibility references.
     */
    private function validateLauncherModConfiguration(mixed $validator, array $item, string $prefix): void
    {
        $groups = collect((array)($item['launcher_mod_groups'] ?? []))
            ->filter(static fn(mixed $group): bool => is_array($group) && filled($group['key'] ?? null))
            ->keyBy(static fn(array $group): string => trim((string)$group['key']));

        $mods = collect((array)($item['launcher_mods'] ?? []))
            ->filter(static fn(mixed $mod): bool => is_array($mod));

        $baseVersion = trim((string)($item['version'] ?? ''));
        $actualUpdateVersions = collect((array)($item['launcher_updates'] ?? []))
            ->filter(static fn(mixed $update): bool => is_array($update))
            ->map(static fn(array $update): string => trim((string)($update['version'] ?? '')))
            ->filter()
            ->unique()
            ->values()
            ->all();

        foreach ($mods->values() as $modIndex => $mod) {
            $groupKey = trim((string)($mod['group_key'] ?? ''));

            if ($groupKey !== '' && !$groups->has($groupKey)) {
                $validator->errors()->add(
                    "{$prefix}.launcher_mods.{$modIndex}.group_key",
                    'گروه انتخاب‌شده برای این ماد در همین Sale Item وجود ندارد.'
                );
            }

            $baseVersions = $this->versionList($mod['compatible_base_versions'] ?? []);
            $updateVersions = $this->versionList($mod['compatible_update_versions'] ?? []);

            if (filter_var($mod['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN) && $baseVersions === []) {
                $validator->errors()->add(
                    "{$prefix}.launcher_mods.{$modIndex}.compatible_base_versions_text",
                    'برای ماد منتشرشده حداقل یک نسخه پایه سازگار باید مشخص شود.'
                );
            }

            foreach ($baseVersions as $version) {
                if ($baseVersion === '' || $version !== $baseVersion) {
                    $validator->errors()->add(
                        "{$prefix}.launcher_mods.{$modIndex}.compatible_base_versions_text",
                        "نسخه پایه {$version} متعلق به این Sale Item نیست."
                    );
                }
            }

            foreach ($updateVersions as $version) {
                if (!in_array($version, $actualUpdateVersions, true)) {
                    $validator->errors()->add(
                        "{$prefix}.launcher_mods.{$modIndex}.compatible_update_versions_text",
                        "آپدیت {$version} برای این Sale Item ثبت نشده است."
                    );
                }
            }
        }

        $iranGroup = $groups->get(self::IRAN_PLAYER_STRENGTH_GROUP_KEY);

        if (!is_array($iranGroup)) {
            return;
        }

        if (trim((string)($iranGroup['title'] ?? '')) !== self::IRAN_PLAYER_STRENGTH_GROUP_TITLE) {
            $validator->errors()->add(
                "{$prefix}.launcher_mod_groups",
                'عنوان گروه iran_player_strength باید «' . self::IRAN_PLAYER_STRENGTH_GROUP_TITLE . '» باشد.'
            );
        }

        if (!filter_var($iranGroup['is_exclusive'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $validator->errors()->add(
                "{$prefix}.launcher_mod_groups",
                'گروه iran_player_strength باید انحصاری (is_exclusive=true) باشد.'
            );
        }

        $iranMods = $mods
            ->filter(static fn(array $mod): bool => trim((string)($mod['group_key'] ?? '')) === self::IRAN_PLAYER_STRENGTH_GROUP_KEY)
            ->values();

        $registeredNames = $iranMods
            ->map(static fn(array $mod): string => trim((string)($mod['name'] ?? '')))
            ->filter()
            ->sort()
            ->values()
            ->all();

        $expectedNames = collect(self::IRAN_PLAYER_STRENGTH_MOD_NAMES)->sort()->values()->all();

        if ($registeredNames !== $expectedNames) {
            $validator->errors()->add(
                "{$prefix}.launcher_mods",
                'گروه iran_player_strength باید دقیقا شامل سه ماد «سطح عادی»، «سطح پیشرفته» و «سطح حرفه‌ای» باشد.'
            );
        }

        foreach ($iranMods as $modIndex => $mod) {
            if (($mod['install_target'] ?? null) !== LauncherModInstallTargetEnum::WINDOWS_DOCUMENTS->value) {
                $validator->errors()->add(
                    "{$prefix}.launcher_mods.{$modIndex}.install_target",
                    'مادهای گروه سطح قدرت بازیکنان باید در windows_documents نصب شوند.'
                );
            }
        }
    }

    /**
     * Determine whether the submitted base package is a split archive.
     * Explicit manifest configuration wins; otherwise multipart 7z names
     * are used for the same automatic detection as the manifest sync action.
     *
     * @param array<string, mixed> $manifest
     * @param array<int, mixed> $files
     */
    private function isSplitArchivePayload(array $manifest, array $files): bool
    {
        $downloadType = trim((string)($manifest['download_type'] ?? ''));

        if ($downloadType !== '') {
            return $downloadType === 'split_archive';
        }

        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }

            $fileName = basename(str_replace(
                '\\',
                '/',
                trim((string)($file['file_name'] ?? ''))
            ));

            if (preg_match('/\.7z\.\d{3,}$/i', $fileName) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Both-or-neither, exact 32-byte key, uniqueness of content_key_id across ALL
     * manifests, AND a separate vault-password grant for any REAL change to the key
     * value -- see RevealContentKeyController/RevealContentKeyAction. The vault gate
     * is enforced HERE, server-side, against the database's current value -- not by
     * trusting whatever the submitted form fields say -- specifically so an admin
     * cannot bypass it by editing the DOM to re-enable a field the front end left
     * disabled.
     */
    private function validateContentKey(
        mixed   $validator,
        array   $manifest,
        string  $prefix,
        ?int    $currentSaleItemId,
        bool    $supportsSider,
    ): void
    {
        $contentKeyId = is_string($manifest['content_key_id'] ?? null)
            ? trim($manifest['content_key_id'])
            : null;
        $contentKeyValue = is_string($manifest['content_key_value'] ?? null)
            ? trim($manifest['content_key_value'])
            : null;

        $existing = $currentSaleItemId !== null
            ? ProductSaleItemManifest::query()
                ->where('product_sale_item_id', $currentSaleItemId)
                ->first(['content_key_id', 'content_key_value'])
            : null;

        // The admin panel redacts content_key_value to blank on page load and only
        // fills it in after a successful vault unlock. A blank value submitted
        // alongside the SAME content_key_id already on file means "left locked,
        // unchanged" -- SyncProductSaleItemManifestAction preserves the existing
        // value in exactly this case, so there is nothing to validate here.
        if (blank($contentKeyValue)
            && filled($contentKeyId)
            && $existing !== null
            && $existing->content_key_id === $contentKeyId) {
            return;
        }

        if (blank($contentKeyId) && blank($contentKeyValue)) {
            return;
        }

        if (blank($contentKeyId)) {
            $validator->errors()->add(
                "{$prefix}.manifest.content_key_id",
                'وقتی مقدار کلید محتوا وارد شده، شناسه کلید هم الزامی است.'
            );
        }

        if (blank($contentKeyValue)) {
            $validator->errors()->add(
                "{$prefix}.manifest.content_key_value",
                'برای ثبت این شناسه، مقدار کلید هم لازم است -- یا صندوق کلید محتوا را باز کنید تا مقدار فعلی حفظ شود.'
            );
        }

        if (blank($contentKeyId) || blank($contentKeyValue)) {
            return;
        }

        $decoded = base64_decode($contentKeyValue, true);

        if ($decoded === false || strlen($decoded) !== 32) {
            $validator->errors()->add(
                "{$prefix}.manifest.content_key_value",
                'کلید محتوا باید یک رشته Base64 معتبر باشد که دقیقاً به ۳۲ بایت (۲۵۶ بیت) رمزگشایی شود.'
            );
        }

        // Sharing a content_key_id across sale item variants is allowed on purpose --
        // e.g. PGL6's half-season and full-season editions legitimately ship the
        // identical database until their update paths diverge, and re-encrypting it
        // twice under two different ids would be pure waste. What must never happen is
        // the SAME id pointing at two DIFFERENT keys, which would make the launcher's
        // lookup ambiguous (see IssueOfflineLicenseAction's own defensive check for
        // this same invariant on the read side).
        // content_key_value is encrypted at rest (non-deterministic ciphertext even
        // for the same plaintext), so the comparison MUST happen after Eloquent
        // decrypts it in PHP -- a SQL "!= $contentKeyValue" against the raw column
        // would compare plaintext to ciphertext and never match correctly.
        $conflictingValue = ProductSaleItemManifest::query()
            ->where('content_key_id', $contentKeyId)
            ->when(
                $currentSaleItemId !== null,
                fn($query) => $query->where('product_sale_item_id', '!=', $currentSaleItemId)
            )
            ->get(['content_key_value'])
            ->contains(fn(ProductSaleItemManifest $row): bool => $row->content_key_value !== $contentKeyValue);

        if ($conflictingValue) {
            $validator->errors()->add(
                "{$prefix}.manifest.content_key_id",
                'این شناسه قبلاً با یک مقدار کلید متفاوت برای نسخه فروش دیگری ثبت شده است. برای اشتراک یک کلید بین چند نسخه، مقدار کلید هم باید دقیقاً یکی باشد؛ برای دیتابیس متفاوت، شناسه‌ی جداگانه انتخاب کنید.'
            );
        }

        if (!$supportsSider) {
            $validator->errors()->add(
                "{$prefix}.manifest.content_key_id",
                'تحویل کلید محتوا فقط از طریق Sider انجام می‌شود؛ ابتدا پشتیبانی از Sider را برای این نسخه فعال کنید.'
            );
        }

        $isRealChange = $existing === null || $existing->content_key_value !== $contentKeyValue;

        if ($isRealChange && !$this->hasContentKeyVaultGrant()) {
            $validator->errors()->add(
                "{$prefix}.manifest.content_key_value",
                'برای ثبت یا تغییر کلید محتوا، ابتدا باید رمز صندوق کلید محتوا را در همین صفحه وارد کنید.'
            );
        }
    }

    private function hasContentKeyVaultGrant(): bool
    {
        $unlockedUntil = $this->session()?->get('content_key_vault_unlocked_until');

        return is_int($unlockedUntil) && $unlockedUntil > now()->timestamp;
    }

    private function isSafeRelativeWindowsPath(string $path): bool
    {
        $normalized = str_replace('\\', '/', trim($path));

        if ($normalized === '' || $normalized === '.') {
            return true;
        }

        if (str_starts_with($normalized, '/')
            || preg_match('/^[a-zA-Z]:/', $normalized) === 1) {
            return false;
        }

        return !in_array('..', explode('/', $normalized), true);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان',
            'slug' => 'اسلاگ',
            'status' => 'وضعیت',
            'store_id' => 'فروشنده',
            'seller_commission_percent' => 'درصد سهم فروشگاه',
            'addons.*.seller_commission_percent' => 'درصد سهم فروشگاه از خدمت افزوده',
            'body' => 'توضیحات',
            'excerpt' => 'خلاصه',
            'video_gallery_items.*.title' => 'عنوان ویدیو',
            'video_gallery_items.*.video_url' => 'لینک ویدیو',
            'video_gallery_items.*.cover.id' => 'کاور ویدیو',
            'sale_items.*.base_price' => 'قیمت پایه آیتم فروش',
            'sale_items.*.title' => 'عنوان آیتم فروش',
            'sale_items.*.edition_key' => 'کلید نسخه آیتم فروش',
            'sale_items.*.version' => 'نسخه آیتم فروش',
            'sale_items.*.discount.discounted_price' => 'مبلغ با تخفیف',
            'sale_items.*.discount.starts_at' => 'تاریخ شروع تخفیف',
            'sale_items.*.discount.ends_at' => 'تاریخ پایان تخفیف',
            'sale_items.*.discount.show_special_box' => 'نمایش باکس تخفیف ویژه',
            'sale_items.*.discount.special_box_title' => 'عنوان باکس تخفیف ویژه',
            'sale_items.*.discount.special_box_description' => 'توضیحات باکس تخفیف ویژه',
            'sale_items.*.manifest.download_type' => 'نوع دانلود',
            'sale_items.*.manifest.archive_format' => 'فرمت آرشیو',
            'sale_items.*.manifest.assembled_file_name' => 'نام آرشیو نهایی',
            'sale_items.*.manifest.install_directory' => 'مسیر نصب',
            'sale_items.*.manifest.main_executable_path' => 'مسیر فایل اجرایی',
            'sale_items.*.manifest.working_directory' => 'پوشه اجرا',
            'sale_items.*.manifest.launch_arguments' => 'آرگومان‌های اجرا',
            'sale_items.*.manifest.sider_executable_path' => 'مسیر فایل اجرایی Sider',
            'sale_items.*.manifest.sider_working_directory' => 'پوشه اجرای Sider',
            'sale_items.*.manifest.sider_arguments' => 'آرگومان‌های Sider',
            'sale_items.*.manifest.inject_dll_path' => 'مسیر فایل DLL تزریق',
            'sale_items.*.manifest.content_key_id' => 'شناسه کلید محتوا',
            'sale_items.*.manifest.content_key_value' => 'مقدار کلید محتوا',
            'sale_items.*.manifest.cpu_compatibility.default_mode' => 'حالت پیش‌فرض CPU Compatibility',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.enum' => 'وضعیت انتخاب‌شده معتبر نیست.',
            'store_id.required' => 'انتخاب فروشنده الزامی است.',
            'store_id.exists' => 'فروشنده انتخاب‌شده معتبر نیست.',
            'video_gallery_items.*.title.required' => 'عنوان ویدیو الزامی است.',
            'video_gallery_items.*.video_url.required' => 'لینک ویدیو الزامی است.',
            'video_gallery_items.*.video_url.url' => 'لینک ویدیو باید یک آدرس معتبر http یا https باشد.',
            'video_gallery_items.*.cover.required' => 'انتخاب کاور برای ویدیو الزامی است.',
            'video_gallery_items.*.cover.id.required' => 'انتخاب کاور برای ویدیو الزامی است.',
            'video_gallery_items.*.cover.id.exists' => 'کاور انتخاب‌شده معتبر نیست.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $product = $this->route('product');

        $defaultStoreId = $this->input('store_id')
            ?: $product?->store_id
                ?: Store::query()->orderBy('id')->value('id');

        $tags = $this->input('tags', []);

        $existing = collect(data_get($tags, 'existing', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->all();

        $new = collect(data_get($tags, 'new', []))
            ->filter(fn($title) => filled($title))
            ->map(fn($title) => preg_replace('/\s+/', ' ', trim((string)$title)))
            ->filter()
            ->unique(fn($title) => mb_strtolower($title))
            ->values()
            ->all();

        $saleItems = $this->normalizeSaleItemsForValidation();

        $submittedPublishedAt = trim((string) $this->input('published_at', ''));
        $existingPublishedAt = $product?->published_at;
        $publishedAt = match (true) {
            $submittedPublishedAt === '' && $product !== null => $existingPublishedAt?->format('Y-m-d H:i:s'),
            $submittedPublishedAt === '' => now()->format('Y-m-d H:i:s'),
            $existingPublishedAt !== null
                && $submittedPublishedAt === jalali_date($existingPublishedAt)
                => $existingPublishedAt->format('Y-m-d H:i:s'),
            default => gregorian_date($submittedPublishedAt),
        };

        $this->merge([
            'store_id' => $defaultStoreId,
            'picture_id' => $this->input('picture.id'),
            'tags' => [
                'existing' => $existing,
                'new' => $new,
            ],
            'sale_items' => $saleItems,
            'published_at' => $publishedAt,
            'update_progress.started_at' => $this->input('update_progress.started_at')
                ? gregorian_date($this->input('update_progress.started_at'))
                : null,
            'update_progress.release_at' => $this->input('update_progress.release_at')
                ? gregorian_date($this->input('update_progress.release_at'))
                : null,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeSaleItemsForValidation(): array
    {
        return collect($this->input('sale_items', []))
            ->filter(
                static fn(mixed $item): bool => is_array($item)
            )
            ->map(function (array $item): array {
                $discount = data_get($item, 'discount', []);

                if (!is_array($discount)) {
                    $discount = [];
                }

                $item['id'] = filled(data_get($item, 'id')) ? $this->normalizeIntegerInput(data_get($item, 'id')) : null;
                $item['title'] = $this->normalizeNullableStringInput(data_get($item, 'title'), 255);
                $item['edition_key'] = $this->normalizeNullableStringInput(data_get($item, 'edition_key'), 80);
                $item['version'] = $this->normalizeNullableStringInput(data_get($item, 'version'), 50);
                $item['sku'] = $this->normalizeNullableStringInput(data_get($item, 'sku'), 255);
                $item['base_price'] = $this->normalizeIntegerInput(data_get($item, 'base_price')) ?? 0;
                $item['download_limit'] = $this->normalizeIntegerInput(data_get($item, 'download_limit'));
                $item['download_expires_in_days'] = $this->normalizeIntegerInput(data_get($item, 'download_expires_in_days'));
                $item['max_quantity_per_order'] = $this->normalizeIntegerInput(data_get($item, 'max_quantity_per_order'));
                $item['description'] = $this->normalizeNullableStringInput(data_get($item, 'description'));
                $item['launcher_enabled'] = $this->normalizeBooleanInput(data_get($item, 'launcher_enabled'), false);
                $item['launcher_free_claim_enabled'] = $this->normalizeBooleanInput(
                    data_get($item, 'launcher_free_claim_enabled'),
                    false
                );
                $item['launcher_title'] = $this->normalizeNullableStringInput(data_get($item, 'launcher_title'), 255);
                $item['launcher_excerpt'] = $this->normalizeNullableStringInput(data_get($item, 'launcher_excerpt'), 2000);
                $item['launcher_cover_media_id'] = $this->normalizeIntegerInput(
                    data_get($item, 'launcher_hero.id')
                );
                $item['launcher_sort_order'] = $this->normalizeIntegerInput(data_get($item, 'launcher_sort_order')) ?? 0;
                $item['sort_order'] = $this->normalizeIntegerInput(data_get($item, 'sort_order')) ?? 0;
                $item['discount'] = [
                    'id' => filled(data_get($discount, 'id')) ? $this->normalizeIntegerInput(data_get($discount, 'id')) : null,
                    'discounted_price' => $this->normalizeIntegerInput(data_get($discount, 'discounted_price')),
                    'starts_at' => $this->normalizeJalaliDateTimeInput(data_get($discount, 'starts_at')),
                    'ends_at' => $this->normalizeJalaliDateTimeInput(data_get($discount, 'ends_at')),
                    'show_special_box' => $this->normalizeBooleanInput(data_get($discount, 'show_special_box'), false),
                    'special_box_title' => $this->normalizeNullableStringInput(data_get($discount, 'special_box_title'), 255),
                    'special_box_description' => $this->normalizeNullableStringInput(data_get($discount, 'special_box_description'), 1000),
                ];
                $item['manifest'] = $this->normalizeManifestInput(data_get($item, 'manifest'));
                if (array_key_exists('launcher_updates', $item)) {
                    $item['launcher_updates'] = collect(data_get($item, 'launcher_updates', []))
                        ->filter(fn($update) => is_array($update))
                        ->map(function (array $update): array {
                            $update['archive_password'] = $this->normalizeArchivePasswordInput(
                                $update['archive_password'] ?? null
                            );
                            return $update;
                        })
                        ->values()
                        ->all();
                }
                $item['launcher_mods'] = collect(data_get($item, 'launcher_mods', []))
                    ->filter(fn($mod) => is_array($mod))
                    ->map(function (array $mod): array {
                        $mod['archive_password'] = $this->normalizeArchivePasswordInput(
                            $mod['archive_password'] ?? null
                        );
                        $mod['install_target'] = $this->normalizeLauncherModInstallTarget(
                            $mod['install_target'] ?? null
                        );
                        $mod['install_mode'] = strtolower(trim((string)($mod['install_mode'] ?? ''))) === 'permanent'
                            ? 'permanent'
                            : 'toggle';
                        $mod['compatible_base_versions'] = $this->versionList(
                            $mod['compatible_base_versions_text'] ?? $mod['compatible_base_versions'] ?? []
                        );
                        $mod['compatible_update_versions'] = $this->versionList(
                            $mod['compatible_update_versions_text'] ?? $mod['compatible_update_versions'] ?? []
                        );
                        return $mod;
                    })
                    ->values()
                    ->all();

                $item['launcher_support_enabled'] = $this->normalizeBooleanInput(
                    data_get($item, 'launcher_support_enabled'),
                    false
                );

                $item['launcher_support_url'] = $this->normalizeNullableStringInput(
                    data_get($item, 'launcher_support_url'),
                    2048
                );

                $item['launcher_usage_guide_enabled'] = $this->normalizeBooleanInput(
                    data_get($item, 'launcher_usage_guide_enabled'),
                    false
                );

                // normalizeNullableStringInput collapses '\s+' (including every '\n') into a
                // single space -- fine for a title, fatal for multi-line admin-authored text:
                // every line break the admin typed was destroyed before the launcher's
                // RichTextParser ever saw the string, which is why it rendered as one run-on
                // line. normalizeMultilineListInput only trims the edges, same fix already
                // used for the protection-policy path lists.
                $item['launcher_usage_guide_content'] = mb_substr(
                    (string)($this->normalizeMultilineListInput(
                        data_get($item, 'launcher_usage_guide_content')
                    ) ?? ''),
                    0,
                    20000
                ) ?: null;

                return $item;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeManifestInput(
        mixed $value,
    ): array
    {
        $manifest = is_array($value) ? $value : [];

        $capabilities = data_get($manifest, 'capabilities', []);

        if (!is_array($capabilities)) {
            $capabilities = [];
        }

        $installOptions = data_get($manifest, 'install_options', []);

        if (!is_array($installOptions)) {
            $installOptions = [];
        }

        $cpuCompatibility = data_get($manifest, 'cpu_compatibility', []);

        if (!is_array($cpuCompatibility)) {
            $cpuCompatibility = [];
        }

        $requiresSider = $this->normalizeBooleanInput(
            data_get($manifest, 'requires_sider'),
            false
        );

        $supportsSider = $this->normalizeBooleanInput(
                data_get($capabilities, 'supports_sider'),
                $requiresSider
            ) || $requiresSider;

        return [
            /*
             * Null or empty means automatic detection.
             */
            'download_type' => $this->normalizeDownloadTypeInput(data_get($manifest, 'download_type')),
            'archive_format' => $this->normalizeArchiveFormatInput(data_get($manifest, 'archive_format')),
            'archive_password' => $this->normalizeArchivePasswordInput(data_get($manifest, 'archive_password')),
            'assembled_file_name' => $this->normalizeNullableStringInput(data_get($manifest, 'assembled_file_name'), 255),
            'install_directory' => $this->normalizeNullableStringInput(data_get($manifest, 'install_directory'), 500),
            'install_folder_name' => $this->normalizeNullableStringInput(data_get($manifest, 'install_folder_name'), 150),
            'requires_administrator' => $this->normalizeBooleanInput(data_get($manifest, 'requires_administrator'), false),
            'has_option_file' => $this->normalizeBooleanInput(data_get($manifest, 'has_option_file'), false),
            'main_executable_path' => $this->normalizeNullableStringInput(data_get($manifest, 'main_executable_path'), 500),
            'working_directory' => $this->normalizeNullableStringInput(data_get($manifest, 'working_directory'), 500) ?? '.',
            'launch_arguments' => $this->normalizeNullableStringInput(data_get($manifest, 'launch_arguments'), 2000),
            'requires_sider' => $requiresSider,
            'sider_executable_path' => $this->normalizeNullableStringInput(data_get($manifest, 'sider_executable_path'), 500),
            'sider_working_directory' => $this->normalizeNullableStringInput(data_get($manifest, 'sider_working_directory'), 500) ?? '.',
            'sider_arguments' => $this->normalizeNullableStringInput(data_get($manifest, 'sider_arguments'), 2000),
            'run_sider_as_administrator' => $this->normalizeBooleanInput(data_get($manifest, 'run_sider_as_administrator'), false),
            // Lost silently before this fix: this function rebuilds the whole manifest
            // array from an explicit key list, and content_key_id/content_key_value were
            // never added to it when the content-key feature shipped -- so both fields
            // were stripped here, in prepareForValidation(), BEFORE validateContentKey()
            // or SyncProductSaleItemManifestAction ever saw them, regardless of what the
            // browser actually submitted.
            'content_key_id' => $this->normalizeNullableStringInput(data_get($manifest, 'content_key_id'), 128),
            'content_key_value' => $this->normalizeNullableStringInput(data_get($manifest, 'content_key_value'), 512),

            // The content-protection-policy fields hit the EXACT SAME bug described in
            // the comment above, just later: they were added to the admin form, to
            // UpdateProductRequest's own rules(), and to SyncProductSaleItemManifestAction,
            // but never to THIS explicit key list -- so prepareForValidation() silently
            // dropped every one of them before validation ever ran, regardless of what
            // the browser submitted, and the manifest's protected_* columns stayed NULL
            // on every save no matter how complete the posted form data was.
            //
            // normalizeNullableStringInput is deliberately NOT used for the three path
            // lists or the JSON blob: it collapses all whitespace (including newlines)
            // into single spaces, which would merge a 19-line path list into one
            // unparseable line before normalizeStringList ever got to split it back
            // apart. These need their newlines/JSON structure left alone.
            'protected_folder_markers' => $this->normalizeMultilineListInput(
                data_get($manifest, 'protected_folder_markers')
            ),
            'protected_exact_paths' => $this->normalizeMultilineListInput(
                data_get($manifest, 'protected_exact_paths')
            ),
            'protected_watermark_sites' => $this->normalizeMultilineListInput(
                data_get($manifest, 'protected_watermark_sites')
            ),
            'protected_excluded_paths' => $this->normalizeMultilineListInput(
                data_get($manifest, 'protected_excluded_paths')
            ),
            // protected_modules: passed through as-is for an API/artisan caller sending a
            // real array. protected_modules_json: what the admin form actually posts (one
            // hidden field, JSON-encoded) -- SyncProductSaleItemManifestAction prefers
            // protected_modules when both are present and falls back to decoding this.
            'protected_modules' => is_array(data_get($manifest, 'protected_modules'))
                ? data_get($manifest, 'protected_modules')
                : null,
            'protected_modules_json' => $this->normalizeRawJsonStringInput(
                data_get($manifest, 'protected_modules_json')
            ),
            'protected_strict_modules' => $this->normalizeBooleanInput(
                data_get($manifest, 'protected_strict_modules'),
                true
            ),
            'protected_enforce_native_dlls' => $this->normalizeBooleanInput(
                data_get($manifest, 'protected_enforce_native_dlls'),
                false
            ),
            'cpu_compatibility' => [
                'enabled' => $this->normalizeBooleanInput(data_get($cpuCompatibility, 'enabled'), false),
                'default_mode' => $this->normalizeCpuCompatibilityModeInput(
                    data_get($cpuCompatibility, 'default_mode')
                ),
                'apply_to_game' => $this->normalizeBooleanInput(data_get($cpuCompatibility, 'apply_to_game'), false),
                'apply_to_sider' => $this->normalizeBooleanInput(data_get($cpuCompatibility, 'apply_to_sider'), false),
                'allow_user_override' => $this->normalizeBooleanInput(data_get($cpuCompatibility, 'allow_user_override'), false),
            ],
            'capabilities' => [
                'supports_install' => $this->normalizeBooleanInput(data_get($capabilities, 'supports_install'), true),
                'supports_launch' => $this->normalizeBooleanInput(data_get($capabilities, 'supports_launch'), true),
                'supports_sider' => $supportsSider,
                'supports_settings' => $this->normalizeBooleanInput(data_get($capabilities, 'supports_settings'), false),
                'supports_profiles' => $this->normalizeBooleanInput(data_get($capabilities, 'supports_profiles'), false),
                'supports_update' => $this->normalizeBooleanInput(data_get($capabilities, 'supports_update'), false),
                'supports_mod_manager' => $this->normalizeBooleanInput(data_get($capabilities, 'supports_mod_manager'), false),
            ],
            'install_options' => [
                'extract_enabled' => $this->normalizeBooleanInput(data_get($installOptions, 'extract_enabled'), true),
                'delete_archives_after_install' => $this->normalizeBooleanInput(data_get($installOptions, 'delete_archives_after_install'), true),
                'ask_before_delete_archives' => $this->normalizeBooleanInput(data_get($installOptions, 'ask_before_delete_archives'), true),
                'clean_temp_after_install' => $this->normalizeBooleanInput(data_get($installOptions, 'clean_temp_after_install'), true),
                'create_desktop_shortcut' => $this->normalizeBooleanInput(data_get($installOptions, 'create_desktop_shortcut'), false),
                'required_install_space_bytes' => $this->normalizeByteSizeInput(data_get($installOptions, 'required_install_space_bytes')),
                'estimated_installed_size_bytes' => $this->normalizeByteSizeInput(data_get($installOptions, 'estimated_installed_size_bytes')),
            ],
        ];
    }

    private function normalizeByteSizeInput(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = filter_var($value, FILTER_VALIDATE_INT);

        return $value !== false && $value >= 0 ? $value : null;
    }

    private function normalizeLauncherModInstallTarget(mixed $value): string
    {
        $value = strtolower(trim((string)$value));

        return in_array($value, LauncherModInstallTargetEnum::values(), true)
            ? $value
            : LauncherModInstallTargetEnum::PRODUCT_ROOT->value;
    }

    private function normalizeCpuCompatibilityModeInput(mixed $value): string
    {
        $value = strtolower(trim((string)$value));

        return in_array(
            $value,
            ProductSaleItemManifest::CPU_COMPATIBILITY_MODES,
            true
        )
            ? $value
            : ProductSaleItemManifest::DEFAULT_CPU_COMPATIBILITY['default_mode'];
    }

    private function normalizeDownloadTypeInput(mixed $value): ?string
    {
        $value = trim((string)$value);

        return in_array(
            $value,
            [
                'single_file',
                'multiple_files',
                'split_archive',
            ],
            true
        ) ? $value : null;
    }

    private function versionList(mixed $value): array
    {
        $values = is_array($value)
            ? $value
            : preg_split('/[\s,،]+/u', (string)$value, -1, PREG_SPLIT_NO_EMPTY);

        return collect($values)
            ->map(fn($version) => trim((string)$version))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeArchiveFormatInput(mixed $value): ?string
    {
        $value = strtolower(trim((string)$value));

        return in_array(
            $value,
            [
                '7z',
            ],
            true
        ) ? $value : null;
    }

    private function normalizeNullableStringInput(mixed $value, ?int $maxLength = null): ?string
    {
        if (!filled($value)) {
            return null;
        }

        $value = preg_replace(
            '/\s+/u',
            ' ',
            trim((string)$value)
        );

        if (!is_string($value) || $value === '') {
            return null;
        }

        if ($maxLength !== null) {
            $value = mb_substr(
                $value,
                0,
                $maxLength
            );
        }

        return $value;
    }

    // Trims the value but -- unlike normalizeNullableStringInput -- never touches
    // internal whitespace. Used for the newline-delimited protection-policy lists,
    // where collapsing "\r\n" runs into single spaces would merge every path onto
    // one unparseable line before normalizeStringList (in
    // SyncProductSaleItemManifestAction) ever gets to split it back apart.
    private function normalizeMultilineListInput(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    // Same reasoning as normalizeMultilineListInput: the JSON structure (and the
    // digests/paths inside it) must reach json_decode byte-for-byte, so this only
    // trims surrounding whitespace and never collapses anything internal.
    private function normalizeRawJsonStringInput(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalizeArchivePasswordInput(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        // فاصله‌های داخل رمز بخشی از خود رمز هستند و نباید مانند متن عادی فشرده شوند.
        return mb_substr($value, 0, 512);
    }

    private function normalizeIntegerInput(mixed $value): ?int
    {
        if (!filled($value)) {
            return null;
        }

        $value = $this->normalizeDigits((string)$value);
        $value = str_replace([',', '٬', ' '], '', $value);

        return is_numeric($value) ? max(0, (int)$value) : null;
    }

    private function normalizeBooleanInput(mixed $value, bool $fallback = false): bool
    {
        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $normalized ?? $fallback;
    }

    private function normalizeJalaliDateTimeInput(mixed $value): ?string
    {
        if (!filled($value)) {
            return null;
        }

        $value = $this->normalizeDigits(trim((string)$value));
        $value = str_replace('T', ' ', $value);

        return gregorian_date($value);
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);
    }
}
