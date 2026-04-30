<?php

namespace App\Domain\WordPressMigration\Models;

use Illuminate\Database\Eloquent\Model;

class WpMigrationMap extends Model
{
    protected $fillable = [
        'source_type',
        'source_id',
        'target_type',
        'target_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
