<?php

namespace App\Domain\Players\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PglPlayerChange extends Model
{
    protected $table = 'pgl_player_changes';

    protected $fillable = ['import_id', 'pid', 'name', 'type', 'payload'];

    protected $casts = ['payload' => 'array'];

    public function import(): BelongsTo
    {
        return $this->belongsTo(PglImport::class);
    }

    public function faType(): string
    {
        return match ($this->type) {
            'overall' => 'اورال',
            'position' => 'پست',
            'age' => 'سن',
            'height' => 'قد',
            'weight' => 'وزن',
            'stats' => 'آمار',
            'transfer' => 'انتقال',
            default => $this->type,
        };
    }
}
