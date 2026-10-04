@php
    $items = config('admin-sidebar', []);

    $sidebarBadges = [
        'pending_comments' => \App\Domain\Discussion\Models\Comment::query()
            ->where('status', \App\Domain\Discussion\Enums\CommentStatusEnum::PENDING)
            ->count(),
        'open_tickets' => \App\Domain\Support\Models\Ticket::query()
            ->where('status', \App\Domain\Support\Enums\TicketStatusEnum::OPEN)
            ->count(),
        'unresolved_wallet_withdrawals' => \Illuminate\Support\Facades\Gate::allows('wallet.withdrawals')
            ? \App\Domain\Financial\Models\WalletWithdrawal::query()
                ->unresolved()
                ->count()
            : 0,
        'pending_store_withdrawals' => \Illuminate\Support\Facades\Gate::allows('store.withdrawals')
            ? \App\Domain\Marketplace\Models\StoreWithdrawal::query()
                ->where('status', \App\Domain\Marketplace\Enums\StoreWithdrawalStatusEnum::PENDING)
                ->count()
            : 0,
    ];
    $sidebarBadges['pending_financial_requests'] = $sidebarBadges['unresolved_wallet_withdrawals'] + $sidebarBadges['pending_store_withdrawals'];
@endphp

<aside
    class="sidebar bg-white text-gray-900 shadow"
    id="Sidebar"
>
    <a href="{{ route('home') }}"
       class="sidebar__brand flex items-center gap-[10px] pt-[8px] pb-[4px] px-[10px] mb-[4px] transition hover:opacity-70">
        <img src="{{ url('img/logo.webp') }}" alt="لوگو" width="42" height="42">
        <div class="flex flex-col gap-[2px]">
            <div class="text-[0.9rem] font-bold">مودینگ وی</div>
            <div class="text-[11px] opacity-75">پنل مدیریت پلتفرم</div>
        </div>
    </a>

    <nav class="sidebar__nav">
        <ul class="space-y-2">
            @foreach($items as $item)
                <x-admin.sidebar.sidebar-item
                    :item="$item"
                    :badges="$sidebarBadges"
                />
            @endforeach
        </ul>
    </nav>
</aside>
