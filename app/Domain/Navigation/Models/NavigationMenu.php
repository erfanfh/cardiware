<?php

namespace App\Domain\Navigation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationMenu extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'max_depth',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get all items belonging to the menu.
     */
    public function items(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'menu_id');
    }

    /**
     * Get root items of the menu.
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'menu_id')
            ->whereNull('parent_id')
            ->with([
                'children.children',
            ])
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
