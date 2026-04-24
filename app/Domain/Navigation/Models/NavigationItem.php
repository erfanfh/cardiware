<?php

namespace App\Domain\Navigation\Models;

use App\Domain\Navigation\Enums\NavigationItemTargetEnum;
use App\Domain\Navigation\Enums\NavigationItemTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NavigationItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'title',
        'url',
        'target',
        'reference_id',
        'reference_type',
        'icon',
        'badge',
        'css_class',
        'is_mega_menu',
        'mega_menu_title',
        'mega_menu_columns',
        'mega_menu_style',
        'sort_order',
        'depth',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => NavigationItemTypeEnum::class,
        'target' => NavigationItemTargetEnum::class,
        'is_mega_menu' => 'boolean',
        'mega_menu_columns' => 'integer',
        'sort_order' => 'integer',
        'depth' => 'integer',
        'settings' => 'array',
    ];

    /**
     * Get the menu that owns the item.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(NavigationMenu::class, 'menu_id');
    }

    /**
     * Get the parent navigation item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get children of the current item.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->with(['children', 'reference'])
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Get the referenced model for the item.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
