<?php

namespace App\Domain\Players\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PglImport extends Model
{
    protected $table = 'pgl_imports';

    protected $fillable = ['label', 'source', 'players_count', 'teams_count',
                           'changes_count', 'file_sha256'];

    protected $casts = [
        'players_count' => 'integer',
        'teams_count' => 'integer',
        'changes_count' => 'integer',
    ];

    public function changes(): HasMany
    {
        return $this->hasMany(PglPlayerChange::class);
    }
}
