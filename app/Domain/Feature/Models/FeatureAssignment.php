<?php

namespace App\Domain\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeatureAssignment extends Model
{
    protected $fillable = [
        'feature_definition_id',
        'subject_id',
        'subject_type',
        'value',
    ];

    /**
     * @return BelongsTo
     */
    public function featureDefinition(): BelongsTo
    {
        return $this->belongsTo(FeatureDefinition::class);
    }

    /**
     * @return MorphTo
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
