<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductInformationField extends Model
{
    protected $fillable = [
        'title',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(ProductInformationEntry::class);
    }
}
