<?php

namespace App\Application\Raffle\Actions\Front;

use App\Application\Raffle\Actions\Draw\VerifyDrawRunAction;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Models\DrawRun;
use Illuminate\Support\Facades\DB;

class BuildPublicDrawVerificationReportAction
{
    public function __construct(private readonly VerifyDrawRunAction $verify) {}

    public function execute(DrawRun $run): array
    {
        abort_unless($run->status === DrawRunStatusEnum::COMPLETED && filled($run->seed_reveal), 404);

        $run->loadMissing(['campaign', 'winners.ticket', 'winners.prize']);
        $metadata = $run->metadata ?? [];
        $query = DB::table('draw_tickets')
            ->where('campaign_id', $run->campaign_id)
            ->where('status', 'active');

        $excludedTicketIds = array_map('intval', $metadata['excluded_ticket_ids'] ?? []);
        $excludedUserIds = array_map('intval', $metadata['excluded_user_ids'] ?? []);
        if ($excludedTicketIds !== []) {
            $query->whereNotIn('id', $excludedTicketIds);
        }
        if ($excludedUserIds !== []) {
            $query->whereNotIn('user_id', $excludedUserIds);
        }

        $eligibleTickets = $query
            ->orderBy('id')
            ->get(['id', 'public_token', 'user_id', 'code'])
            ->map(fn ($ticket): array => [
                'ticket_id' => (int) $ticket->id,
                'ticket_public_token' => (string) $ticket->public_token,
                'user_id' => (int) $ticket->user_id,
                'ticket_code' => (string) $ticket->code,
            ])
            ->all();

        $result = $this->verify->execute($run);

        return [
            'report_version' => 1,
            'generated_at' => now()->toIso8601String(),
            'campaign' => [
                'public_token' => $run->campaign->public_token,
                'title' => $run->campaign->title,
                'slug' => $run->campaign->slug,
            ],
            'draw_run' => [
                'public_token' => $run->public_token,
                'type' => $run->type->value,
                'algorithm' => $run->algorithm,
                'seed_commitment' => $run->seed_commitment,
                'seed_reveal' => $run->seed_reveal,
                'eligible_ticket_count' => (int) $run->eligible_ticket_count,
                'eligible_set_hash' => $run->eligible_set_hash,
                'allow_multiple_wins_per_user' => (bool) ($metadata['allow_multiple_wins_per_user'] ?? false),
                'excluded_ticket_ids' => $excludedTicketIds,
                'excluded_user_ids' => $excludedUserIds,
                'completed_at' => $run->completed_at?->toIso8601String(),
            ],
            'algorithm_specification' => [
                'score' => 'HMAC-SHA256(seed, "raffle-draw-v1|{campaign_public_token}|{ticket_public_token}")',
                'ranking' => 'Ascending hexadecimal score; ticket_id is the deterministic tie-breaker.',
                'eligible_set_hash_rows' => 'ticket_id + 0x1F + ticket_public_token + 0x1F + user_id + 0x1F + ticket_code + newline, ordered by ticket_id.',
            ],
            'verification' => [
                'valid' => $result->valid,
                'seed_commitment_valid' => $result->seedCommitmentValid,
                'eligible_set_hash_valid' => $result->eligibleSetHashValid,
                'eligible_ticket_count_valid' => $result->eligibleTicketCountValid,
                'winner_set_valid' => $result->winnerSetValid,
                'errors' => $result->errors,
            ],
            'eligible_tickets' => $eligibleTickets,
            'selected_winners' => $run->winners
                ->sortBy(fn ($winner): string => sprintf('%010d|%s|%010d', $winner->prize_id, $winner->kind->value, $winner->position))
                ->values()
                ->map(fn ($winner): array => [
                    'prize' => $winner->prize?->title,
                    'kind' => $winner->kind->value,
                    'position' => (int) $winner->position,
                    'ticket_id' => (int) $winner->ticket_id,
                    'ticket_public_token' => $winner->ticket?->public_token,
                    'ticket_code' => $winner->ticket?->code,
                    'score' => (string) ($winner->metadata['score'] ?? ''),
                ])
                ->all(),
        ];
    }
}
