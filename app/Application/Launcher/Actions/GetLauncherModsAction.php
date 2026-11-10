<?php

namespace App\Application\Launcher\Actions;

use App\Application\Commerce\Exceptions\PurchaseAccessFileUnavailableException;
use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use Illuminate\Support\Facades\Log;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Launcher\Enums\LauncherModInstallTargetEnum;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherUpdate;
use Illuminate\Support\Collection;

class GetLauncherModsAction
{
    public function __construct(
        private readonly RemoteDownloadFileMetadataResolver $metadataResolver,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(PurchaseAccess $access): array
    {
        $saleItem = $access->productSaleItem ?? $access->productSaleItem()->firstOrFail();
        $baseVersion = (string) $saleItem->version;
        $updateState = $this->updateState($saleItem, (int) $access->user_id);

        return LauncherMod::query()
            ->where('product_sale_item_id', $access->product_sale_item_id)
            ->published()
            ->with([
                'group',
                'image.primaryVariant',
                'files',
            ])
            ->orderBy('priority')
            ->orderBy('id')
            ->get()
            ->map(function (LauncherMod $mod) use ($baseVersion, $updateState): ?array {
                try {
                    return $this->modPayload(
                        $mod,
                        $baseVersion,
                        $updateState['current_version'],
                        $updateState['versions'],
                    );
                } catch (PurchaseAccessFileUnavailableException $exception) {
                    // One mod with a bad file row (URL with a space, host not in the allow
                    // list, file_name != URL basename) used to turn the WHOLE list into a
                    // 503 -- and the launcher shows that as "0 mods" for every product.
                    // Skip just that mod and say which one in the log.
                    Log::warning('launcher.mods.skipped_unavailable_file', [
                        'mod_id' => $mod->id,
                        'slug' => $mod->slug,
                        'files' => $mod->files->map(fn (LauncherModFile $f): array => [
                            'file_name' => $f->file_name,
                            'url' => $f->url,
                        ])->all(),
                    ]);
                    return null;
                }
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{current_version: ?string, versions: array<int, string>}
     */
    private function updateState(ProductSaleItem $saleItem, ?int $userId): array
    {
        /** @var Collection<int, LauncherUpdate> $updates */
        $updates = LauncherUpdate::query()
            ->where('product_sale_item_id', $saleItem->getKey())
            ->visibleToLauncherUser($userId, $saleItem->staging_user_ids ?? [])
            ->with('files')
            ->get();

        $versions = $updates
            ->map(static fn (LauncherUpdate $update): string => trim((string) $update->version))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $currentVersion = $updates
            ->filter(static function (LauncherUpdate $update): bool {
                return (bool) $update->is_enabled
                    && $update->published_at !== null
                    && $update->published_at->lte(now())
                    && $update->hasDownloadablePackage();
            })
            ->sort(function (LauncherUpdate $left, LauncherUpdate $right): int {
                $publishedAtComparison = ($left->published_at?->getTimestamp() ?? 0)
                    <=> ($right->published_at?->getTimestamp() ?? 0);

                if ($publishedAtComparison !== 0) {
                    return $publishedAtComparison;
                }

                $versionComparison = version_compare($left->version, $right->version);

                return $versionComparison !== 0
                    ? $versionComparison
                    : $left->getKey() <=> $right->getKey();
            })
            ->values()
            ->last()?->version;

        return [
            'current_version' => filled($currentVersion) ? (string) $currentVersion : null,
            'versions' => $versions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function modPayload(
        LauncherMod $mod,
        string $baseVersion,
        ?string $currentUpdateVersion,
        array $actualUpdateVersions,
    ): array {
        $baseVersions = array_values(array_intersect(
            $this->versionList($mod->compatible_base_versions),
            $baseVersion !== '' ? [$baseVersion] : [],
        ));
        $updateVersions = array_values(array_intersect(
            $this->versionList($mod->compatible_update_versions),
            $actualUpdateVersions,
        ));

        $isBaseCompatible = $baseVersion !== ''
            && in_array($baseVersion, $baseVersions, true);

        $isUpdateCompatible = $currentUpdateVersion === null
            ? true
            : in_array($currentUpdateVersion, $updateVersions, true);

        $files = $mod->files
            ->values()
            ->map(fn (LauncherModFile $file): array => $this->filePayload($file));

        $totalSizeBytes = (int) $files->sum('size_bytes');

        if ((int) $mod->size_bytes !== $totalSizeBytes) {
            $mod->forceFill(['size_bytes' => $totalSizeBytes])->saveQuietly();
        }

        $variant = $mod->image?->primaryVariant;
        $imageUrl = $mod->image?->url;

        return [
            'id' => $mod->id,
            'name' => $mod->name,
            'slug' => $mod->slug,
            'short_description' => $mod->short_description,
            'description' => $mod->description,
            'version' => $mod->version,
            'image_url' => $imageUrl,
            'image' => $imageUrl ? [
                'url' => $imageUrl,
                'width' => $variant?->width !== null ? (int) $variant->width : null,
                'height' => $variant?->height !== null ? (int) $variant->height : null,
            ] : null,
            'size_bytes' => $totalSizeBytes,
            'priority' => (int) $mod->priority,
            'is_required' => (bool) $mod->is_required,
            // هشدار فعال‌سازی (مثلاً افت فریم)؛ لانچر قبل از فعال‌سازی به کاربر نشان می‌دهد.
            'warning' => filled($mod->warning_text) ? [
                'text' => (string) $mod->warning_text,
                'level' => $mod->warning_level ?: 'warning',
            ] : null,
            'group' => $mod->group ? [
                'key' => $mod->group->key,
                'title' => $mod->group->title,
                'is_exclusive' => (bool) $mod->group->is_exclusive,
            ] : null,
            // بلوک مستقل از کاربر؛ وضعیت قفل/باز بودن به ازای هر کاربر در
            // ResolveLauncherModAccessForUserAction روی همین بلوک سوار می‌شود.
            'access' => [
                'type' => ($mod->access_type ?? \App\Domain\Launcher\Enums\LauncherModAccessTypeEnum::FREE)->value,
                'price_toman' => $mod->isPurchaseAccess() ? (int) $mod->price_toman : null,
            ],
            'compatibility' => [
                'base_version' => $baseVersion !== '' ? $baseVersion : null,
                'update_version' => $currentUpdateVersion,
                'base_versions' => $baseVersions,
                'update_versions' => $updateVersions,
                'is_compatible' => $isBaseCompatible && $isUpdateCompatible,
            ],
            'package' => [
                'archive_format' => '7z',
                'archive_password' => $mod->archive_password,
                'merge_mode' => 'merge_to_product_root',
                'install_target' => ($mod->install_target ?? LauncherModInstallTargetEnum::PRODUCT_ROOT)->value,
                // 'toggle' (enable/ + disable/, cached, switchable) or 'permanent' (enable/ only,
                // moved into the game folder once, launcher hides the disable action). Launchers
                // older than 1.21.56 ignore this key and treat every mod as toggle.
                'install_mode' => ($mod->install_mode ?? \App\Domain\Launcher\Enums\LauncherModInstallModeEnum::TOGGLE)->value,
                'total_size_bytes' => $totalSizeBytes,
                'files' => $files->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filePayload(LauncherModFile $file): array
    {
        $metadata = $this->metadataResolver->resolve(
            sourceType: 'mod',
            file: $file,
            url: (string) $file->url,
        );

        return [
            'file_id' => $file->id,
            'file_name' => basename(str_replace('\\', '/', (string) $file->file_name)),
            'title' => $file->title,
            'size_bytes' => $metadata['size_bytes'],
            'sha256' => $metadata['sha256'],
            'mime_type' => $metadata['mime_type'] ?: $file->mime_type,
            'part_number' => $file->part_number !== null ? (int) $file->part_number : null,
            'is_required' => (bool) $file->is_required,
            'order' => (int) $file->sort_order,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function versionList(mixed $value): array
    {
        return collect((array) $value)
            ->map(static fn (mixed $version): string => trim((string) $version))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
