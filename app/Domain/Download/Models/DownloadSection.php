<?php

namespace App\Domain\Download\Models;

use App\Domain\Content\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DownloadSection extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'title',
        'description',
        'password',
        'sort_order',
        'copy_all_links_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'post_id' => 'integer',
        'sort_order' => 'integer',
        'copy_all_links_count' => 'integer',
    ];

    /**
     * Get the post that owns the download section.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the entries of the download section.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(DownloadEntry::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
