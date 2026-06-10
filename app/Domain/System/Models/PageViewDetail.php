<?php

namespace App\Domain\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageViewDetail extends Model
{
    protected $fillable = [
        'pageable_type',
        'pageable_id',
        'count',
        'date',
    ];

    protected $casts = [
        'count' => 'integer',
        'date' => 'date',
    ];

    public function pageable(): MorphTo
    {
        return $this->morphTo();
    }
}
