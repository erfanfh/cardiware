@auth
    @php
        $accountAnnouncements = app(\App\Application\Notification\GetAccountAnnouncementsAction::class)->execute();
    @endphp
    @if($accountAnnouncements->isNotEmpty())
        <section class="mb-6 space-y-3" aria-label="اطلاعیه‌های مدیریت">
            @foreach($accountAnnouncements as $announcement)
                @php
                    $appearance = match ($announcement->type) {
                        'warning' => ['panel' => 'border-amber-200 bg-amber-50/80 dark:border-amber-400/20 dark:bg-amber-400/10', 'icon' => 'bg-amber-100 text-amber-700 dark:bg-amber-400/15 dark:text-amber-300'],
                        'success' => ['panel' => 'border-emerald-200 bg-emerald-50/80 dark:border-emerald-400/20 dark:bg-emerald-400/10', 'icon' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300'],
                        default => ['panel' => 'border-sky-200 bg-sky-50/80 dark:border-sky-400/20 dark:bg-sky-400/10', 'icon' => 'bg-sky-100 text-sky-700 dark:bg-sky-400/15 dark:text-sky-300'],
                    };
                @endphp
                <article class="flex min-w-0 items-start gap-3 rounded-2xl border p-4 sm:gap-4 sm:p-5 {{ $appearance['panel'] }}" aria-labelledby="account-announcement-{{ $announcement->id }}">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $appearance['icon'] }} [&_svg]:h-5 [&_svg]:w-5" aria-hidden="true">
                        @include('icons.notification-01')
                    </span>
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">اطلاعیه مدیریت · {{ \App\Domain\Notification\Models\AccountAnnouncement::TYPES[$announcement->type] ?? 'اطلاع‌رسانی' }}</span>
                        <h2 id="account-announcement-{{ $announcement->id }}" class="mt-1 break-words text-sm font-extrabold leading-7 text-slate-900 dark:text-white">{{ $announcement->title }}</h2>
                        <p class="mt-1 whitespace-pre-line break-words text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $announcement->body }}</p>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
@endauth
