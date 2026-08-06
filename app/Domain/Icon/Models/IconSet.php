<?php

namespace App\Domain\Icon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IconSet extends Model
{
    protected $fillable = [
        'key',
        'label',
        'source_file',
        'checksum',
        'icons_count',
        'imported_at',
    ];

    protected $casts = [
        'icons_count' => 'integer',
        'imported_at' => 'datetime',
    ];

    public function icons(): HasMany
    {
        return $this->hasMany(Icon::class);
    }
}
