<?php

namespace App\Domain\System\Models;

use Illuminate\Database\Eloquent\Model;

class PageViewDetail extends Model
{
    protected $fillable = [
        'pageable_type',
        'pageable_id',
        'count',
        'date',
    ];
}
