<?php

namespace App\Application\Media\Actions;

use App\Domain\Media\Enums\MediaFileTypeEnum;
use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListImageLibraryAction
{
    /**
     * Retrieve a paginated list of image media files.
     */
    public function execute(?string $search = null, int $perPage = 24): LengthAwarePaginator
    {
        $query = MediaFile::query()
            ->with([
                'primaryVariant',
                'variants' => fn($builder) => $builder
                    ->where('variant', MediaFileVariantEnum::THUMBNAIL),
            ])
            ->where('type', MediaFileTypeEnum::IMAGE)
            ->whereNotNull('primary_variant_id')
            ->latest('id');

        if (!empty($search)) {
            $search = trim($search);

            $query->where(function ($sub) use ($search) {
                if (ctype_digit($search)) {
                    $sub->orWhere('id', (int)$search);
                }

                $sub->orWhere('title', 'like', "%{$search}%");

                $sub->orWhereHas('variants', function ($variantQuery) use ($search) {
                    $variantQuery->where('path', 'like', "%{$search}%")
                        ->orWhere('checksum', 'like', "%{$search}%");
                });
            });
        }

        return $query->paginate($perPage);
    }
}
