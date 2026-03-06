<?php

namespace App\Domain\Feature\Models;

use App\Domain\Feature\Enums\ValueTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureDefinition extends Model
{
    protected $fillable = [
        'key',
        'title',
        'value_type',
        'allowed_values',
    ];

    protected $casts = [
        'value_type' => ValueTypeEnum::class,
    ];

    /**
     * @return HasMany
     */
    public function featureAssignments(): HasMany
    {
        return $this->hasMany(FeatureAssignment::class);
    }
}
