<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProductMediaCollectionEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMedia extends Model
{
    protected $fillable = [
        'product_id',
        'media_file_id',
        'collection',
        'sort_order',
        'title',
        'meta',
    ];

    protected $casts = [
        'collection' => ProductMediaCollectionEnum::class,
        'sort_order' => 'integer',
        'meta' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

}
