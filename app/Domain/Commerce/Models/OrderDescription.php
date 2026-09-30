<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDescription extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'created_by_user_id',
        'body',
    ];

    protected $casts = [
        'is_legacy' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
