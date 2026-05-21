<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'status',
    ];

    protected $casts = [
        'status' => StoreStatusEnum::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
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
