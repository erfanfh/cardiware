<?php

namespace App\Domain\Organization\Models;

use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'job_title',
        'bio',
        'picture_id',
        'email',
        'mobile',
        'website_url',
    ];

    protected function fullName(): Attribute
    {
        return Attribute::get(
            fn(): string => trim(
                $this->first_name . ' ' . $this->last_name
            )
        );
    }

    /**
     * @return BelongsTo
     */
    public function picture(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }
}
