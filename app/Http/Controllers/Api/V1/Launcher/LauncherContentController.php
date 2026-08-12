<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LauncherContentController extends Controller
{
    public function updates(Request $request, int $purchaseAccess): JsonResponse
    {
        $access = $this->access($request, $purchaseAccess);

        $updates = LauncherUpdate::query()
            ->where('product_sale_item_id', $access->product_sale_item_id)
            ->published()
            ->with('files')
            ->get()
            ->filter(fn(LauncherUpdate $update): bool => $update->hasDownloadablePackage())
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
            ->map(function (LauncherUpdate $update): array {
                $files = $update->files->values();

                return [
                    'id' => $update->id,
                    'version' => $update->version,
                    'title' => $update->title,
                    'description' => $update->description,
                    'changelog' => $update->changelog,
                    'is_mandatory' => $update->is_mandatory,
                    'has_option_file' => $update->has_option_file,
                    'option_file' => $update->resolvedOptionFile(),
                    'published_at' => $update->published_at?->toISOString(),
                    'package' => [
                        'archive_format' => $update->archive_format,
                        'merge_mode' => 'merge_to_product_root',
                        'total_size_bytes' => (int)$files->sum(
                            static fn($file): int => (int)$file->size_bytes
                        ),
                        'files' => $files
                            ->map(fn($file): array => $this->file($file))
                            ->values(),
                    ],
                ];
            });

        return response()->json([
            'data' => $updates->all(),
        ]);
    }

    public function mods(Request $request, int $purchaseAccess): JsonResponse
    {
        $access = $this->access($request, $purchaseAccess);
        $baseVersion = $access->productSaleItem()->value('version');

        $mods = LauncherMod::query()
            ->where('product_sale_item_id', $access->product_sale_item_id)
            ->published()
            ->with(['group', 'image', 'files'])
            ->orderBy('priority')
            ->orderBy('id')
            ->get()
            ->map(function (LauncherMod $mod) use ($baseVersion): array {
                $baseVersions = $mod->compatible_base_versions ?? [];
                $isCompatible = $baseVersions === []
                    || in_array($baseVersion, $baseVersions, true);

                return [
                    'id' => $mod->id,
                    'name' => $mod->name,
                    'slug' => $mod->slug,
                    'short_description' => $mod->short_description,
                    'description' => $mod->description,
                    'version' => $mod->version,
                    'image_url' => $mod->image?->url,
                    'size_bytes' => $mod->size_bytes ?: (int)$mod->files->sum('size_bytes'),
                    'priority' => $mod->priority,
                    'is_required' => $mod->is_required,
                    'group' => $mod->group ? [
                        'key' => $mod->group->key,
                        'title' => $mod->group->title,
                        'is_exclusive' => $mod->group->is_exclusive,
                    ] : null,
                    'compatibility' => [
                        'base_versions' => $baseVersions,
                        'update_versions' => $mod->compatible_update_versions ?? [],
                        'is_compatible' => $isCompatible,
                    ],
                    'package' => [
                        'archive_format' => '7z',
                        'merge_mode' => 'merge_to_product_root',
                        'files' => $mod->files->map(fn($file): array => $this->file($file))->values(),
                    ],
                ];
            });

        return response()->json(['data' => $mods->isEmpty() ? new \stdClass() : $mods]);
    }

    private function access(Request $request, int $id): PurchaseAccess
    {
        $access = PurchaseAccess::query()
            ->withoutGlobalScope(ActivePurchaseAccessScope::class)
            ->whereKey($id)
            ->where('user_id', $request->user()->getKey())
            ->whereHas(
                'productSaleItem',
                fn($query) => $query->where('launcher_enabled', true)
            )
            ->firstOrFail();

        abort_unless($access->isUsable(), 403);

        return $access;
    }

    private function file(mixed $file): array
    {
        return [
            'file_id' => $file->id,
            'file_name' => $file->file_name,
            'title' => $file->title,
            'size_bytes' => (int)$file->size_bytes,
            'sha256' => strtolower((string)$file->sha256),
            'mime_type' => $file->mime_type,
            'part_number' => $file->part_number,
            'is_required' => $file->is_required,
            'order' => $file->sort_order,
        ];
    }
}
