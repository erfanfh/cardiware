<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Icon\Models\Icon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductInformationField extends Model
{
    protected $fillable = [
        'title',
        'icon_id',
    ];

    public function icon(): BelongsTo
    {
        return $this->belongsTo(Icon::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ProductInformationEntry::class);
    }
}
