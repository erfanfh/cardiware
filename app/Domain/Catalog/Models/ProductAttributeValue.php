<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttributeValue extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'product_attribute_id',
        'title',
    ];

    /**
     * @return BelongsTo
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    /**
     * @return HasMany
     */
    public function assignmentValues(): HasMany
    {
        return $this->hasMany(ProductAttributeAssignmentValue::class);
    }
}
