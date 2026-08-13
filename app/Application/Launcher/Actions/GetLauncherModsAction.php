<?php

namespace App\Application\Launcher\Actions;

use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Domain\Commerce\Models\PurchaseAccess;
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
        $baseVersion = (string) $access->productSaleItem()->value('version');
        $updateState = $this->updateState($access->product_sale_item_id);

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
            ->map(fn (LauncherMod $mod): array => $this->modPayload(
                $mod,
                $baseVersion,
                $updateState['current_version'],
                $updateState['versions'],
            ))
            ->values()
            ->all();
    }

    /**
     * @return array{current_version: ?string, versions: array<int, string>}
     */
    private function updateState(int $saleItemId): array
    {
        /** @var Collection<int, LauncherUpdate> $updates */
        $updates = LauncherUpdate::query()
            ->where('product_sale_item_id', $saleItemId)
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
            'group' => $mod->group ? [
                'key' => $mod->group->key,
                'title' => $mod->group->title,
                'is_exclusive' => (bool) $mod->group->is_exclusive,
            ] : null,
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
