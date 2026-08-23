<?php

namespace App\Domain\Poll\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Poll\Enums\PollAudienceEnum;
use App\Domain\Poll\Enums\PollResultsVisibilityEnum;
use App\Domain\Poll\Enums\PollStatusEnum;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Poll extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'question', 'description', 'status', 'audience',
        'results_visibility', 'allows_multiple_selections', 'min_selections',
        'max_selections', 'allow_vote_changes', 'randomize_options',
        'show_response_count', 'starts_at', 'ends_at', 'created_by_user_id', 'metadata',
    ];

    protected $casts = [
        'status' => PollStatusEnum::class,
        'audience' => PollAudienceEnum::class,
        'results_visibility' => PollResultsVisibilityEnum::class,
        'allows_multiple_selections' => 'boolean',
        'min_selections' => 'integer',
        'max_selections' => 'integer',
        'allow_vote_changes' => 'boolean',
        'randomize_options' => 'boolean',
        'show_response_count' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'responses_count' => 'integer',
        'views_count' => 'integer',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Poll $poll): void {
            $poll->public_token ??= (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_token';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeOptions(): HasMany
    {
        return $this->options()->where('is_active', true);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PollResponse::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(PollView::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', PollStatusEnum::PUBLISHED);
    }

    public function acceptsVotesAt(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        return $this->status === PollStatusEnum::PUBLISHED
            && (! $this->starts_at || $at->gte($this->starts_at))
            && (! $this->ends_at || $at->lt($this->ends_at));
    }

    public function hasEnded(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        return $this->ends_at !== null && $at->gte($this->ends_at);
    }

    public function canShowResults(bool $hasVoted, bool $isAdmin = false): bool
    {
        if ($isAdmin) {
            return true;
        }

        return match ($this->results_visibility) {
            PollResultsVisibilityEnum::ALWAYS => true,
            PollResultsVisibilityEnum::AFTER_VOTE => $hasVoted,
            PollResultsVisibilityEnum::AFTER_END => $this->hasEnded(),
            PollResultsVisibilityEnum::ADMIN_ONLY => false,
        };
    }
}
