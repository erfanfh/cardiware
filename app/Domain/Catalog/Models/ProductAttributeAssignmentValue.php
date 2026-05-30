<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeAssignmentValue extends Model
{
    protected $fillable = [
        'product_attribute_assignment_id',
        'product_attribute_value_id',
        'sort_order',
    ];

    /**
     * @return BelongsTo
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeAssignment::class, 'product_attribute_assignment_id');
    }

    /**
     * @return BelongsTo
     */
    public function value(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeValue::class, 'product_attribute_value_id');
    }
}
