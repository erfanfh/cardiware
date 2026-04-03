<?php

namespace App\Domain\Feature\Traits;

use App\Domain\Feature\Models\FeatureAssignment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFeatures
{
    /**
     * @return MorphMany
     */
    public function featureAssignments(): MorphMany
    {
        return $this->morphMany(FeatureAssignment::class, 'subject');
    }
}
