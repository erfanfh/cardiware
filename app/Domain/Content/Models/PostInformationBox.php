<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\PostInformationBoxTypeEnum;
use Illuminate\Database\Eloquent\Model;

class PostInformationBox extends Model
{
    protected $fillable = [
        'type',
        'title',
        'body',
    ];

    protected $casts = [
        'type' => PostInformationBoxTypeEnum::class,
    ];
}
