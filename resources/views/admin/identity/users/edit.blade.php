@extends('admin.layout')

@section('title', 'ویرایش کاربر')

@section('content')
    <x-admin.form action="{{ route('admin.users.update', $user) }}" method="put">
        @include('admin.identity.users.partials.form-fields', [
            'user' => $user,
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>

    {{-- تایید/رد معرفی باشگاه VIP کاربر --}}
    @if(filled($user->club_bio) || $user->club_bio_status === 'pending')
        <x-admin.metabox title="معرفی باشگاه VIP (نیازمند تایید)" class="mt-5">
            @php
                $statusLabels = [
                    'none' => ['ثبت نشده', 'bg-slate-100 text-slate-600'],
                    'pending' => ['در انتظار تایید', 'bg-amber-100 text-amber-800'],
                    'approved' => ['تایید شده', 'bg-emerald-100 text-emerald-700'],
                    'rejected' => ['رد شده', 'bg-rose-100 text-rose-700'],
                ];
                $statusKey = (string)($user->club_bio_status ?? 'none');
                [$statusLabel, $statusClass] = $statusLabels[$statusKey] ?? $statusLabels['none'];
            @endphp
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-xl px-3 py-1.5 text-xs font-black {{ $statusClass }}">{{ $statusLabel }}</span>
                    @if($user->club_bio_edited_at)
                        <span class="text-[11px] font-bold text-slate-400">آخرین ویرایش کاربر: {{ jalali_date($user->club_bio_edited_at) }}</span>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-bold leading-7 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    {{ filled($user->club_bio) ? '«' . $user->club_bio . '»' : '— کاربر متن را حذف کرده است —' }}
                    <div class="mt-2 text-[11px] font-bold text-slate-400">تم انتخابی: {{ \App\Application\Loyalty\Support\VipClubProfile::theme($user->club_theme)['label'] }}</div>
                </div>

                @if($statusKey !== 'approved')
                    <form method="POST" action="{{ route('admin.users.club-bio.moderate', $user) }}" class="inline-block"
                          onsubmit="return confirm('معرفی این کاربر تایید شود و روی کارت عمومی نمایش داده شود؟')">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-emerald-700">تایید معرفی</button>
                    </form>
                @endif
                @if($statusKey !== 'rejected')
                    <form method="POST" action="{{ route('admin.users.club-bio.moderate', $user) }}" class="inline-block"
                          onsubmit="return confirm('معرفی این کاربر رد شود؟ کاربر می‌تواند یک‌بار دیگر بنویسد.')">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-rose-700">رد معرفی</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.users.club-bio.moderate', $user) }}" class="inline-block"
                      onsubmit="return confirm('متن معرفی کاملاً پاک شود و کاربر از نو امکان نوشتن داشته باشد؟')">
                    @csrf
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">پاک‌سازی کامل</button>
                </form>
                <p class="text-[11px] font-bold leading-6 text-slate-400">
                    متن فقط پس از «تایید» روی کارت عمومی باشگاه نمایش داده می‌شود؛ در حالت «در انتظار» کاربر نمی‌تواند متن یا تم را تغییر دهد و برای ویرایش بعدی باید اشتراک ماهِ بعد را بخرد.
                </p>
            </div>
        </x-admin.metabox>
    @endif

    @can('wallet.adjust')
        <div class="mt-5">
            <x-admin.form.button tag="a" href="{{ route('admin.users.wallet.show', $user) }}" variant="secondary">
                مدیریت موجودی کیف پول
            </x-admin.form.button>
        </div>
    @endcan
@endsection
