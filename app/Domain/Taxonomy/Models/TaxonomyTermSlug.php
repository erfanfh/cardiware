<?php

namespace App\Domain\Taxonomy\Models;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxonomyTermSlug extends Model
{
    protected $fillable = [
        'taxonomy',
        'route_key',
        'is_current',
        'taxonomy_term_id',
    ];

    protected $casts = [
        'taxonomy' => TaxonomyKeyEnum::class,
        'is_current' => 'boolean',
    ];

    /**
     * Related taxonomy term.
     *
     * @return BelongsTo
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(TaxonomyTerm::class, 'taxonomy_term_id');
    }
}
