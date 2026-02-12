<?php

namespace App\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'title',
        'is_default',
        'is_super_admin',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_super_admin' => 'boolean',
    ];

    /**
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
    * Get the default role assigned to newly created users
     * @return Role
     */
    public static function defaultRole(): Role
    {
        return Role::where('is_default', true)->first();
    }

    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
