<?php

namespace App\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'title',
        'label',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the default role assigned to newly created users
     *
     * @return Role
     */
    public static function defaultRole(): Role
    {
        return Role::where('is_default', true)->first();
    }

    /**
     * Get the Super Admin role
     *
     * @return Role
     */
    public static function superAdminRole(): Role
    {
        return Role::where('title', 'super_admin')->first();
    }

    /**
     * Permission relationship
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
