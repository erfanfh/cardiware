<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Support\ProductReleaseContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductRelease extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'excerpt',
        'changes_body',
        'released_at',
        'sort_order',
    ];

    protected $casts = [
        'released_at' => 'date',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getChangesBodyAttribute(?string $value): string
    {
        // Cached releases from before the migration still contain the legacy relation.
        // An explicitly empty string must stay empty when an admin clears the editor.
        return $value ?? ProductReleaseContent::fromChanges(
            $this->releaseChanges->sortBy([['sort_order', 'asc'], ['id', 'asc']])
        );
    }

    public function releaseChanges(): HasMany
    {
        return $this->hasMany(ProductReleaseChange::class);
    }
}
