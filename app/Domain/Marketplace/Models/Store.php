<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Seo\Traits\HasSeoMeta;
use App\Domain\System\Interfaces\PageViewableInterface;
use App\Infrastructure\Traits\HasExcerpt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model implements PageViewableInterface
{
    use SoftDeletes, HasExcerpt, HasSeoMeta;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'activity_field',
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

    public function getExcerptAttribute(): string
    {
        return '';
    }

    public function picture(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'picture_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(StoreUser::class);
    }

    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_users')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
