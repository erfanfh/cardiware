<?php

namespace App\Domain\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageView extends Model
{
    protected $fillable = [
        'pageable_type',
        'pageable_id',
        'count',
    ];

    /**
     * @return MorphTo
     */
    public function pageable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }
}
