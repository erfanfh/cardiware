@php
    $items = config('admin-sidebar', []);

    $sidebarBadges = [
        'pending_comments' => \App\Domain\Discussion\Models\Comment::query()
            ->where('status', \App\Domain\Discussion\Enums\CommentStatusEnum::PENDING)
            ->count(),
        'open_tickets' => \App\Domain\Support\Models\Ticket::query()
            ->where('status', \App\Domain\Support\Enums\TicketStatusEnum::OPEN)
            ->count(),
        'unseen_career_applications' => \Illuminate\Support\Facades\Gate::allows('career-application.list')
            ? \App\Domain\Career\Models\CareerApplication::query()->whereNull('viewed_at')->count()
            : 0,
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
    aria-label="فهرست مدیریت"
>
    <div class="sidebar__brand flex items-center gap-2">
    <a href="{{ route('home') }}"
       class="flex min-w-0 flex-1 items-center gap-[10px] pt-[8px] pb-[4px] px-[10px] mb-[4px] transition hover:opacity-70">
        <img src="{{ url('img/logo.webp') }}" alt="لوگو" width="42" height="42">
        <div class="flex flex-col gap-[2px]">
            <div class="text-[0.9rem] font-bold">مودینگ وی</div>
            <div class="text-[11px] opacity-75">پنل مدیریت پلتفرم</div>
        </div>
    </a>
    <button type="button" id="SidebarClose" class="sidebar__close lg:hidden inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-700" aria-label="بستن منو">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    </button>
    </div>

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
