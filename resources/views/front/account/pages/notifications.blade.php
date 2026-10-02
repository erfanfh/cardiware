@extends('front.account.layout')

@section('page-content')
    <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-black text-slate-900 dark:text-white">اعلان‌های من</h1>
            <form method="post" action="{{ route('account.notifications.read-all') }}">
                @csrf
                <button class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold dark:border-slate-700" type="submit">علامت‌گذاری همه به‌عنوان خوانده‌شده</button>
            </form>
        </div>
        <div class="space-y-2">
            @forelse($notifications as $notification)
                <form method="post" action="{{ route('account.notifications.read', $notification) }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border p-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-800 {{ $notification->read_at ? 'border-slate-200 dark:border-slate-700' : 'border-brand-200 bg-brand-50/50 dark:border-brand-500/30 dark:bg-brand-500/10' }}">
                        <span class="block font-bold text-slate-900 dark:text-white">{{ $notification->title }}</span>
                        @if($notification->body)<span class="mt-1 block text-sm text-slate-600 dark:text-slate-300">{{ $notification->body }}</span>@endif
                        <span class="mt-2 block text-xs text-slate-500">ارسال: {{ jalali_date($notification->sent_at, 'Y/m/d H:i') }}
                            @if($notification->read_at) · خوانده‌شده: {{ jalali_date($notification->read_at, 'Y/m/d H:i') }} @else · خوانده‌نشده @endif
                        </span>
                    </button>
                </form>
            @empty
                <p class="py-10 text-center text-sm text-slate-500">هنوز اعلانی ندارید.</p>
            @endforelse
        </div>
        <div class="mt-5">{{ $notifications->links() }}</div>
    </section>
@endsection
