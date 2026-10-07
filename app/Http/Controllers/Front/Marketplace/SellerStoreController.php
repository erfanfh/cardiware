<?php

namespace App\Http\Controllers\Front\Marketplace;

use App\Application\Marketplace\Actions\GetSellerProductSalesAction;
use App\Application\Support\Actions\CreateTicketMessageAction;
use App\Application\Support\Exceptions\TicketAttachmentUploadException;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Catalog\Scopes\ActiveProductScope;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Support\ReplyTicketRequest;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SellerStoreController extends Controller
{
    public function products(Request $request, Store $store): View
    {
        $search = trim((string) $request->query('q', ''));
        $products = Product::withoutGlobalScope(ActiveProductScope::class)
            ->where('store_id', $store->id)
            ->when($search !== '', fn (Builder $query) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $sales = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.store_id', $store->id)
            ->whereIn('order_items.product_id', $products->getCollection()->modelKeys())
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->select('order_items.product_id')
            ->selectRaw('COUNT(DISTINCT orders.id) AS orders_count')
            ->selectRaw('SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END) AS units_count', ['product_sale_item'])
            ->selectRaw('SUM(CASE WHEN order_items.purchasable_type = ? THEN order_items.quantity ELSE 0 END) AS addon_units_count', [(new ProductAddon())->getMorphClass()])
            ->selectRaw('COALESCE(SUM(order_items.seller_commission_amount), 0) AS seller_amount')
            ->groupBy('order_items.product_id')
            ->get()
            ->keyBy('product_id');

        return view('front.marketplace.seller.products', compact('store', 'products', 'sales', 'search'));
    }

    public function productSales(Request $request, Store $store, int $productId, GetSellerProductSalesAction $salesAction): View
    {
        $product = Product::withoutGlobalScope(ActiveProductScope::class)
            ->where('store_id', $store->getKey())
            ->findOrFail($productId);

        $period = (string) $request->query('period', 'all');
        if (! in_array($period, ['all', '7', '30', '90'], true)) {
            $period = 'all';
        }

        $report = $salesAction->execute($store, $product, $period);

        return view('front.marketplace.seller.product-sales', compact('store', 'product', 'report'));
    }

    public function comments(Request $request, Store $store): View
    {
        $status = (string) $request->query('status', 'all');
        $statuses = ['all', 'pending', 'approved', 'rejected'];
        if (! in_array($status, $statuses, true)) {
            $status = 'all';
        }

        $comments = Comment::query()
            ->where('commentable_type', (new Product())->getMorphClass())
            ->whereIn('commentable_id', $this->productIds($store))
            ->when($request->integer('comment') > 0, fn (Builder $query) => $query->whereKey($request->integer('comment')))
            ->when($status !== 'all', fn (Builder $query) => $query->where('status', $status))
            ->with('user')
            ->latestFirst()
            ->paginate(12)
            ->withQueryString();

        $productNames = Product::withoutGlobalScope(ActiveProductScope::class)
            ->whereIn('id', $comments->getCollection()->pluck('commentable_id'))
            ->pluck('title', 'id');

        return view('front.marketplace.seller.comments', compact('store', 'comments', 'productNames', 'status'));
    }

    public function tickets(Request $request, Store $store): View
    {
        $status = (string) $request->query('status', 'all');
        if (! in_array($status, ['all', 'open', 'answered', 'closed'], true)) {
            $status = 'all';
        }

        $tickets = Ticket::query()
            ->whereIn('product_id', $this->productIds($store))
            ->when($status !== 'all', fn (Builder $query) => $query->where('status', $status))
            ->with(['user', 'product' => fn ($query) => $query->withoutGlobalScope(ActiveProductScope::class)])
            ->latestActivity()
            ->paginate(12)
            ->withQueryString();

        return view('front.marketplace.seller.tickets', compact('store', 'tickets', 'status'));
    }

    public function showTicket(Store $store, Ticket $ticket): View
    {
        $this->authorizeTicket($store, $ticket);
        $ticket->load([
            'user',
            'product' => fn ($query) => $query->withoutGlobalScope(ActiveProductScope::class),
            'messages' => fn ($query) => $query->with(['user', 'attachments.variants'])->orderBy('id'),
        ]);

        return view('front.marketplace.seller.ticket-show', compact('store', 'ticket'));
    }

    public function replyTicket(ReplyTicketRequest $request, Store $store, Ticket $ticket, CreateTicketMessageAction $createMessage): RedirectResponse
    {
        $this->authorizeTicket($store, $ticket);

        try {
            $createMessage->execute(
                ticket: $ticket,
                sender: $request->user(),
                senderType: TicketMessageSenderTypeEnum::SUPPORT,
                body: $request->validated('message'),
                attachments: $request->file('attachments', []),
            );
        } catch (TicketAttachmentUploadException $exception) {
            return back()->withInput()->withErrors(['attachments' => $exception->getMessage()]);
        } catch (DomainException $exception) {
            return back()->withInput()->withErrors(['message' => $exception->getMessage()]);
        }

        return redirect()->route('seller.stores.tickets.show', [$store, $ticket])->with('success', 'پاسخ شما ثبت شد.');
    }

    private function productIds(Store $store): Builder
    {
        return Product::withoutGlobalScope(ActiveProductScope::class)->where('store_id', $store->id)->select('id');
    }

    private function authorizeTicket(Store $store, Ticket $ticket): void
    {
        abort_unless($ticket->product_id && $this->productIds($store)->whereKey($ticket->product_id)->exists(), 404);
    }
}
