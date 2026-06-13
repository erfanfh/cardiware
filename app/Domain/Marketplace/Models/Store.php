<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Media\Models\MediaFile;
use App\Domain\System\Interfaces\PageViewableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model implements PageViewableInterface
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'picture_id',
        'status',
    ];

    protected $casts = [
        'status' => StoreStatusEnum::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function picture(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'picture_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(StoreUser::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
