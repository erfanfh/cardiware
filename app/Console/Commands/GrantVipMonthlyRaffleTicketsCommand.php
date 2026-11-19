<?php

namespace App\Console\Commands;

use App\Application\Raffle\Actions\Ticket\IssueDrawTicketsAction;
use App\Application\Raffle\DTOs\IssueDrawTicketsDTO;
use App\Application\Raffle\Exceptions\DrawCampaignNotAcceptingTicketsException;
use App\Application\Raffle\Exceptions\DrawParticipantIneligibleException;
use App\Application\Raffle\Exceptions\DrawTicketCapacityExceededException;
use App\Application\Raffle\Exceptions\DrawUserTicketLimitExceededException;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\TicketGrant;
use Illuminate\Console\Command;
use Throwable;

class GrantVipMonthlyRaffleTicketsCommand extends Command
{
    protected $signature = 'raffle:grant-vip-monthly-tickets';

    protected $description = 'اهدای یک بلیط ماهانه به اعضای VIP واجد شرایط';

    public function handle(IssueDrawTicketsAction $issueDrawTickets): int
    {
        $now = now();
        $month = $now->format('Y-m');
        $granted = 0;
        $failed = 0;

        $campaigns = DrawCampaign::query()
            ->where('vip_monthly_ticket', true)
            ->where('status', DrawCampaignStatusEnum::OPEN)
            ->lazyById()
            ->filter(fn (DrawCampaign $campaign): bool => $campaign->acceptsTicketsAt($now));

        $vipUsers = User::query()->whereHas('vipSubscriptions', fn ($query) => $query
            ->whereNull('revoked_at')
            ->where('starts_at', '<=', $now)
            ->where('expires_at', '>', $now));

        foreach ($campaigns as $campaign) {
            foreach ($vipUsers->lazyById() as $user) {
                $key = "vip-monthly:{$campaign->getKey()}:{$user->getKey()}:{$month}";
                if (TicketGrant::query()->where('idempotency_key', $key)->exists()) {
                    continue;
                }

                try {
                    $grant = $issueDrawTickets->execute(new IssueDrawTicketsDTO(
                        campaign: $campaign,
                        user: $user,
                        quantity: 1,
                        idempotencyKey: $key,
                        metadata: ['origin' => 'vip_monthly_gift', 'month' => $month],
                    ));
                    $granted += (int) $grant->wasRecentlyCreated;
                } catch (DrawCampaignNotAcceptingTicketsException|DrawTicketCapacityExceededException) {
                    break;
                } catch (DrawParticipantIneligibleException|DrawUserTicketLimitExceededException) {
                    continue;
                } catch (Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            }
        }

        $this->info("VIP monthly gift tickets issued: {$granted}. Failed: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
