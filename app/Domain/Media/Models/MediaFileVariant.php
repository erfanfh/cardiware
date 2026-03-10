<?php

namespace App\Domain\Media\Models;

use App\Domain\Media\Enums\MediaFileDiskEnum;
use App\Domain\Media\Enums\MediaFileVariantEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaFileVariant extends Model
{
    protected $fillable = [
        'media_file_id',
        'disk',
        'mime_type',
        'variant',
        'format',
        'path',
        'byte_size',
        'width',
        'height',
        'checksum',
        'generated_at',
    ];

    protected $casts = [
        'disk' => MediaFileDiskEnum::class,
        'variant' => MediaFileVariantEnum::class,
    ];

    /**
     * @return BelongsTo
     */
    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }
}

