<?php

namespace App\Http\Controllers\Admin\Raffle;

use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\DrawWinner;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DrawCampaignExportController extends Controller
{
    public function tickets(DrawCampaign $drawCampaign): StreamedResponse
    {
        Gate::authorize('raffle-ticket.list');

        return $this->download($drawCampaign, 'tickets', [
            'شناسه', 'کد بلیط', 'وضعیت', 'شناسه کاربر', 'نام کاربر', 'موبایل', 'ایمیل', 'سری صدور', 'زمان صدور', 'زمان ابطال', 'دلیل ابطال',
        ], function ($write) use ($drawCampaign): void {
            DrawTicket::query()
                ->where('campaign_id', $drawCampaign->getKey())
                ->with('user')
                ->orderBy('id')
                ->chunkById(500, function ($tickets) use ($write): void {
                    foreach ($tickets as $ticket) {
                        $write([
                            $ticket->id, $ticket->code, $ticket->status->label(), $ticket->user_id,
                            $ticket->user?->full_name, $ticket->user?->mobile, $ticket->user?->email,
                            $ticket->ticket_grant_id, $ticket->issued_at?->toDateTimeString(),
                            $ticket->invalidated_at?->toDateTimeString(), $ticket->invalidation_reason,
                        ]);
                    }
                });
        });
    }

    public function participants(DrawCampaign $drawCampaign): StreamedResponse
    {
        Gate::authorize('raffle-participant.list');

        return $this->download($drawCampaign, 'participants', [
            'شناسه کاربر', 'نام کاربر', 'موبایل', 'ایمیل', 'بلیط فعال', 'کل بلیط', 'بلیط باطل‌شده', 'تعداد سری صدور', 'آخرین بلیط',
        ], function ($write) use ($drawCampaign): void {
            DB::table('draw_tickets as tickets')
                ->leftJoin('users', 'users.id', '=', 'tickets.user_id')
                ->where('tickets.campaign_id', $drawCampaign->getKey())
                ->groupBy('tickets.user_id', 'users.first_name', 'users.last_name', 'users.display_name', 'users.mobile', 'users.email')
                ->orderBy('tickets.user_id')
                ->selectRaw("tickets.user_id, users.first_name, users.last_name, users.display_name, users.mobile, users.email, SUM(CASE WHEN tickets.status = 'active' THEN 1 ELSE 0 END) as active_count, COUNT(*) as total_count, SUM(CASE WHEN tickets.status = 'invalidated' THEN 1 ELSE 0 END) as invalidated_count, COUNT(DISTINCT tickets.ticket_grant_id) as grants_count, MAX(tickets.issued_at) as last_ticket_at")
                ->chunkById(500, function ($participants) use ($write): void {
                    foreach ($participants as $participant) {
                        $name = trim((string) ($participant->display_name ?: $participant->first_name.' '.$participant->last_name));
                        $write([
                            $participant->user_id, $name, $participant->mobile, $participant->email,
                            $participant->active_count, $participant->total_count, $participant->invalidated_count,
                            $participant->grants_count, $participant->last_ticket_at,
                        ]);
                    }
                }, 'tickets.user_id', 'user_id');
        });
    }

    public function winners(DrawCampaign $drawCampaign): StreamedResponse
    {
        Gate::authorize('raffle-winner.list');

        return $this->download($drawCampaign, 'winners', [
            'شناسه', 'نوع', 'رتبه', 'وضعیت', 'جایزه', 'کد بلیط', 'شناسه کاربر', 'نام کاربر', 'موبایل حساب', 'ایمیل',
            'مهلت تأیید', 'زمان تأیید', 'نام تحویل‌گیرنده', 'موبایل تحویل', 'کد پستی', 'نشانی',
            'زمان پیامک برنده', 'شناسه پیامک برنده', 'خطای پیامک برنده',
            'زمان پیامک یادآوری', 'شناسه پیامک یادآوری', 'خطای پیامک یادآوری', 'زمان تحویل', 'یادداشت تحویل',
        ], function ($write) use ($drawCampaign): void {
            DrawWinner::query()
                ->where('campaign_id', $drawCampaign->getKey())
                ->with(['user', 'ticket', 'prize'])
                ->orderBy('id')
                ->chunkById(250, function ($winners) use ($write): void {
                    foreach ($winners as $winner) {
                        $write([
                            $winner->id, $winner->kind->label(), $winner->position, $winner->status->label(),
                            $winner->prize?->title, $winner->ticket?->code, $winner->user_id,
                            $winner->user?->full_name, $winner->user?->mobile, $winner->user?->email,
                            $winner->confirmation_deadline_at?->toDateTimeString(), $winner->confirmed_at?->toDateTimeString(),
                            $winner->claim_contact_name, $winner->claim_mobile, $winner->claim_postal_code, $winner->claim_address,
                            $winner->winner_sms_sent_at?->toDateTimeString(), $winner->winner_sms_message_id, $winner->winner_sms_failure,
                            $winner->winner_reminder_sms_sent_at?->toDateTimeString(), $winner->winner_reminder_sms_message_id, $winner->winner_reminder_sms_failure,
                            $winner->delivered_at?->toDateTimeString(), $winner->delivery_note,
                        ]);
                    }
                });
        });
    }

    private function download(DrawCampaign $campaign, string $type, array $headers, callable $rows): StreamedResponse
    {
        $filename = "raffle-{$campaign->public_token}-{$type}.csv";

        return response()->streamDownload(function () use ($headers, $rows): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            $write = static fn (array $row) => fputcsv($output, $row, ',', '"', '\\');
            $write($headers);
            $rows($write);
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
