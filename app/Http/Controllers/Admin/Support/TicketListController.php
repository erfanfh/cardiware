<?php

namespace App\Http\Controllers\Admin\Support;

use App\Application\Support\Listings\TicketListing;
use App\Domain\Catalog\Models\Product;
use App\Domain\Support\Enums\TicketStatusEnum;
use App\Domain\Support\Models\Ticket;
use App\Domain\Support\Models\TicketDepartment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Support\IndexTicketRequest;
use App\Infrastructure\Support\BulkActions\TicketBulkActions;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TicketListController extends Controller
{
    public function __invoke(IndexTicketRequest $request, TicketListing $ticketListing): View
    {
        Gate::authorize('ticket.list');

        $baseQuery = Ticket::query();

        $tickets = $ticketListing
            ->handle(
                Ticket::query()->with([
                    'user',
                    'department',
                    'product',
                    'latestMessage.user',
                ])->withCount([
                    'messages as visible_messages_count',
                ]),
                $request->validated()
            )
            ->paginate($ticketListing->perPage())
            ->withQueryString();

        $bulkActionOptions = TicketBulkActions::set()->toOptions();

        $hasActiveFilters = $ticketListing->hasActiveFilters($request->validated());

        $statuses = collect(TicketStatusEnum::cases())
            ->mapWithKeys(fn(TicketStatusEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $departments = TicketDepartment::query()
            ->ordered()
            ->get(['id', 'title'])
            ->mapWithKeys(fn(TicketDepartment $department) => [
                $department->id => $department->title,
            ])
            ->all();

        $statusCounts = [
            'all' => (clone $baseQuery)->count(),
            'open' => (clone $baseQuery)->where('status', TicketStatusEnum::OPEN)->count(),
            'answered' => (clone $baseQuery)->where('status', TicketStatusEnum::ANSWERED)->count(),
            'closed' => (clone $baseQuery)->where('status', TicketStatusEnum::CLOSED)->count(),
        ];

        $selectedProduct = Product::query()
            ->where('id', $request->product_id)
            ->get(['id', 'title'])
            ->map(fn(Product $product) => [
                'value' => (string)$product->id,
                'label' => $product->title,
            ])
            ->values()
            ->all();

        return view('admin.support.tickets.index', compact(
            'tickets',
            'bulkActionOptions',
            'hasActiveFilters',
            'statuses',
            'departments',
            'statusCounts',
            'selectedProduct',
        ));
    }
}
