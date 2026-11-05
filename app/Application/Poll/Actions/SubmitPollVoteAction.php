<?php

namespace App\Application\Poll\Actions;

use App\Application\Poll\Support\PollVoterIdentity;
use App\Application\Shared\Cache\Invalidators\PublicReadCacheInvalidator;
use App\Domain\Poll\Enums\PollAudienceEnum;
use App\Domain\Poll\Models\Poll;
use App\Domain\Poll\Models\PollOption;
use App\Domain\Poll\Models\PollResponse;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitPollVoteAction
{
    /** @param array<int,int|string> $optionIds */
    public function execute(
        Poll $poll,
        PollVoterIdentity $identity,
        array $optionIds,
        ?string $userAgent = null,
        ?string $referrer = null,
    ): PollResponse {
        if (! $poll->acceptsVotesAt()) {
            throw ValidationException::withMessages(['poll' => 'این نظرسنجی در حال حاضر امکان دریافت رأی ندارد.']);
        }

        if ($poll->audience === PollAudienceEnum::MEMBERS && $identity->userId === null) {
            throw ValidationException::withMessages(['poll' => 'برای شرکت در این نظرسنجی باید وارد حساب کاربری شوید.']);
        }

        if ($poll->audience === PollAudienceEnum::CLUB_ELITE) {
            $elite = app(\App\Application\Loyalty\Support\ClubEliteMembership::class);
            $voter = $identity->userId ? \App\Domain\Identity\Models\User::query()->find($identity->userId) : null;

            if (! $elite->isMember($voter)) {
                $levelTitle = $elite->level()?->title;
                throw ValidationException::withMessages(['poll' => $levelTitle
                    ? "این نظرسنجی مخصوص کانون نخبگان باشگاه است (سطح «{$levelTitle}» به بالا)."
                    : 'این نظرسنجی مخصوص کانون نخبگان باشگاه است.']);
            }
        }

        $optionIds = collect($optionIds)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        $count = count($optionIds);

        if (! $poll->allows_multiple_selections && $count !== 1) {
            throw ValidationException::withMessages(['option_ids' => 'لطفاً فقط یک گزینه را انتخاب کنید.']);
        }

        if ($count < $poll->min_selections || $count > $poll->max_selections) {
            throw ValidationException::withMessages([
                'option_ids' => "تعداد گزینه‌های انتخابی باید بین {$poll->min_selections} و {$poll->max_selections} باشد.",
            ]);
        }

        $validIds = PollOption::query()
            ->where('poll_id', $poll->getKey())
            ->where('is_active', true)
            ->whereIn('id', $optionIds)
            ->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (count($validIds) !== $count) {
            throw ValidationException::withMessages(['option_ids' => 'یکی از گزینه‌های انتخاب‌شده معتبر نیست.']);
        }

        return DB::transaction(function () use ($poll, $identity, $validIds, $userAgent, $referrer): PollResponse {
            $response = PollResponse::query()->firstOrCreate(
                ['poll_id' => $poll->getKey(), 'identity_key' => $identity->key],
                [
                    'user_id' => $identity->userId,
                    'ip_hash' => $identity->ipHash,
                    'user_agent' => mb_substr((string) $userAgent, 0, 500) ?: null,
                    'referrer' => mb_substr((string) $referrer, 0, 1000) ?: null,
                    'submitted_at' => now(),
                ],
            );
            $wasCreated = $response->wasRecentlyCreated;
            $response = PollResponse::query()->whereKey($response->getKey())->lockForUpdate()->firstOrFail();

            if (! $wasCreated && ! $poll->allow_vote_changes) {
                throw ValidationException::withMessages(['poll' => 'رأی شما قبلاً در این نظرسنجی ثبت شده است.']);
            }

            $oldIds = $response->options()->pluck('poll_options.id')->map(fn ($id) => (int) $id)->all();
            $added = array_values(array_diff($validIds, $oldIds));
            $removed = array_values(array_diff($oldIds, $validIds));

            app(AuditService::class)->sync($response, 'options', $validIds);
            $response->forceFill([
                'user_id' => $identity->userId,
                'ip_hash' => $identity->ipHash,
                'user_agent' => mb_substr((string) $userAgent, 0, 500) ?: null,
                'referrer' => mb_substr((string) $referrer, 0, 1000) ?: null,
                'submitted_at' => now(),
            ])->save();

            if ($added !== []) {
                PollOption::query()->whereIn('id', $added)->increment('responses_count');
            }
            if ($removed !== []) {
                PollOption::query()->whereIn('id', $removed)->where('responses_count', '>', 0)->decrement('responses_count');
            }
            if ($wasCreated) {
                Poll::query()->whereKey($poll->getKey())->increment('responses_count');
            }

            app(PublicReadCacheInvalidator::class)->forModel(Poll::class);
            app(PublicReadCacheInvalidator::class)->forModel(PollOption::class);

            return $response->load('options');
        });
    }
}
