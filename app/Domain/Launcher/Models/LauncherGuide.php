<?php

namespace App\Domain\Launcher\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LauncherGuide extends Model
{
    protected $fillable = ['title', 'summary', 'content', 'sort_order', 'is_enabled'];

    protected $casts = ['sort_order' => 'integer', 'is_enabled' => 'boolean'];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }
}
