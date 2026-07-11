<?php

namespace App\Domain\Identity\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'is_default',
        'is_super_admin',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_super_admin' => 'boolean',
    ];

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
