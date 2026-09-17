<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUpdateProgress extends Model
{
    public const DEFAULT_TITLE = 'درصد پیشرفت آپدیت محصول';

    public const DEFAULT_DESCRIPTION = 'پیشرفت تا زمان انتشار آپدیت بعدی';

    protected $table = 'product_update_progresses';

    protected $fillable = [
        'product_id',
        'is_visible',
        'title',
        'description',
        'progress_percentage',
        'started_at',
        'release_at',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'progress_percentage' => 'integer',
        'started_at' => 'date',
        'release_at' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isPublished(): bool
    {
        return $this->progress_percentage >= 100;
    }

    public function getDisplayTitleAttribute(): string
    {
        $title = trim((string) $this->title);

        return $title !== '' ? $title : self::DEFAULT_TITLE;
    }

    public function getDisplayDescriptionAttribute(): string
    {
        $description = trim((string) $this->description);

        return $description !== '' ? $description : self::DEFAULT_DESCRIPTION;
    }
}
