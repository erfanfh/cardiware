<?php

namespace App\Domain\Media\Models;


use App\Domain\Identity\Models\User;
use App\Domain\Media\Enums\MediaFileStatusEnum;
use App\Domain\Media\Enums\MediaFileTypeEnum;
use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Enums\MediaFileVisibilityEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    protected $fillable = [
        'type',
        'visibility',
        'title',
        'created_by',
        'primary_variant_id',
        'status',
        'processed_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'type' => MediaFileTypeEnum::class,
        'visibility' => MediaFileVisibilityEnum::class,
        'status' => MediaFileStatusEnum::class,
        'processed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function primaryVariant(): BelongsTo
    {
        return $this->belongsTo(MediaFileVariant::class);
    }

    /**
     * @return HasMany
     */
    public function variants(): HasMany
    {
        return $this->hasMany(MediaFileVariant::class);
    }

    /**
     * @param MediaFileVariantEnum $variant
     * @return MediaFileVariant|null
     */
    public function getVariant(MediaFileVariantEnum $variant): ?MediaFileVariant
    {
        $this->loadMissing('variants');

        return $this->variants->firstWhere('variant', $variant);
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the public URL of the media file.
     *
     * @return string|null
     */
    public function getUrlAttribute(): ?string
    {
        if ($this->visibility !== MediaFileVisibilityEnum::PUBLIC) {
            return null;
        }

        if (!$this->primaryVariant) {
            return null;
        }

        return Storage::disk($this->primaryVariant->disk->value)->url($this->primaryVariant->path);
    }
}

