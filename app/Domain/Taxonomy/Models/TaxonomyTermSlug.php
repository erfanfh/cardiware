<?php

namespace App\Domain\Taxonomy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxonomyTermSlug extends Model
{
    protected $fillable = [
        'type',
        'route_key',
        'is_current',
        'taxonomy_term_id',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(TaxonomyTerm::class, 'taxonomy_term_id');
    }
}
