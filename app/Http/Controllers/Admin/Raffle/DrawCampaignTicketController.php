<?php

namespace App\Http\Controllers\Admin\Raffle;

use App\Application\Raffle\Actions\Ticket\InvalidateDrawTicketAction;
use App\Application\Raffle\Actions\Ticket\InvalidateTicketGrantAction;
use App\Application\Raffle\Actions\Ticket\IssueDrawTicketsAction;
use App\Application\Raffle\DTOs\InvalidateDrawTicketDTO;
use App\Application\Raffle\DTOs\InvalidateTicketGrantDTO;
use App\Application\Raffle\DTOs\IssueDrawTicketsDTO;
use App\Application\Raffle\Listings\DrawTicketListing;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\TicketGrant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Raffle\IndexDrawTicketRequest;
use App\Http\Requests\Admin\Raffle\InvalidateDrawTicketRequest;
use App\Http\Requests\Admin\Raffle\InvalidateTicketGrantRequest;
use App\Http\Requests\Admin\Raffle\IssueDrawTicketsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DrawCampaignTicketController extends Controller
{
    public function index(IndexDrawTicketRequest $request, DrawCampaign $drawCampaign, DrawTicketListing $listing): View
    {
        Gate::authorize('raffle-ticket.list');
        $filters = $request->validated();
        $tickets = $listing->handle(
            DrawTicket::query()->where('campaign_id', $drawCampaign->id)->with(['user','grant.issuer']),
            $filters,
        )->paginate($listing->perPage())->withQueryString();
        $statuses = collect(DrawTicketStatusEnum::cases())->mapWithKeys(fn($status) => [$status->value => $status->label()])->all();
        $hasActiveFilters = $listing->hasActiveFilters($filters);
        return view('admin.raffle.tickets.index', compact('drawCampaign','tickets','statuses','hasActiveFilters'));
    }

    /** @throws Throwable */
    public function issue(IssueDrawTicketsRequest $request, DrawCampaign $drawCampaign, IssueDrawTicketsAction $action): RedirectResponse
    {
        Gate::authorize('raffle-ticket.issue');
        $data = $request->validated();
        $user = User::query()->findOrFail($data['user_id']);
        try {
            $grant = $action->execute(new IssueDrawTicketsDTO(
                campaign: $drawCampaign,
                user: $user,
                quantity: (int)$data['quantity'],
                idempotencyKey: $data['idempotency_key'],
                issuedBy: $request->user(),
                metadata: ['origin' => 'admin_manual', 'reason' => $data['reason']],
            ));
        } catch (\DomainException|\LogicException $exception) {
            return back()->withInput()->with('error', $exception->getMessage())->with('error_title', 'صدور بلیط انجام نشد');
        }
        return redirect()->route('admin.draw-campaigns.tickets.index', $drawCampaign)
            ->with('success', number_format($grant->quantity).' بلیط برای '.$user->full_name.' صادر شد.');
    }

    /** @throws Throwable */
    public function invalidate(InvalidateDrawTicketRequest $request, DrawCampaign $drawCampaign, DrawTicket $drawTicket, InvalidateDrawTicketAction $action): RedirectResponse
    {
        Gate::authorize('raffle-ticket.invalidate');
        abort_unless((int)$drawTicket->campaign_id === (int)$drawCampaign->id, 404);
        try {
            $action->execute(new InvalidateDrawTicketDTO(
                ticket: $drawTicket,
                reason: $request->validated('reason'),
                invalidatedBy: $request->user(),
            ));
        } catch (\DomainException|\LogicException $exception) {
            return back()->with('error', $exception->getMessage())->with('error_title', 'ابطال بلیط انجام نشد');
        }
        return back()->with('success', 'بلیط '.$drawTicket->code.' با موفقیت باطل شد.');
    }

    /** @throws Throwable */
    public function invalidateGrant(
        InvalidateTicketGrantRequest $request,
        DrawCampaign $drawCampaign,
        TicketGrant $ticketGrant,
        InvalidateTicketGrantAction $action,
    ): RedirectResponse {
        Gate::authorize('raffle-ticket.invalidate');
        abort_unless((int) $ticketGrant->campaign_id === (int) $drawCampaign->id, 404);

        try {
            $action->execute(new InvalidateTicketGrantDTO(
                grant: $ticketGrant,
                reason: $request->validated('reason'),
                invalidatedBy: $request->user(),
            ));
        } catch (\DomainException|\LogicException $exception) {
            return back()
                ->with('error', $exception->getMessage())
                ->with('error_title', 'ابطال سری بلیط انجام نشد');
        }

        return back()->with(
            'success',
            'سری بلیط #' . $ticketGrant->id . ' با موفقیت باطل شد.'
        );
    }

}
