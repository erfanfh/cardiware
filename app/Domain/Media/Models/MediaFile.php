<?php

namespace App\Domain\Media\Models;


use App\Domain\Identity\Models\User;
use App\Domain\Media\Enums\MediaFileStatusEnum;
use App\Domain\Media\Enums\MediaFileTypeEnum;
use App\Domain\Media\Enums\MediaFileVisibilityEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'type' => MediaFileTypeEnum::class,
        'visibility' => MediaFileVisibilityEnum::class,
        'status' => MediaFileStatusEnum::class,
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
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

