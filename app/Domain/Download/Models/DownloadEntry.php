<?php

namespace App\Domain\Download\Models;

use App\Domain\Download\Enums\DownloadEntryTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DownloadEntry extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'download_section_id',
        'public_id',
        'type',
        'title',
        'content',
        'sort_order',
        'download_click_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'download_section_id' => 'integer',
        'type' => DownloadEntryTypeEnum::class,
        'sort_order' => 'integer',
        'download_click_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($entry) {
            if (empty($entry->public_id)) {
                do {
                    $token = Str::random(12);
                } while (DownloadEntry::where('public_id', $token)->exists());
                $entry->public_id = $token;
            }
        });
    }

    /**
     * Get the section that owns the download entry.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(DownloadSection::class, 'download_section_id');
    }

    /**
     * Determine whether the entry type is link.
     */
    public function isLink(): bool
    {
        return $this->type === DownloadEntryTypeEnum::LINK;
    }

    /**
     * Determine whether the entry type is text.
     */
    public function isText(): bool
    {
        return $this->type === DownloadEntryTypeEnum::TEXT;
    }
}
