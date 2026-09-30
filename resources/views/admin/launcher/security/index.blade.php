@extends('admin.layout')

@section('title', 'رصد امنیتی لانچر')

@php
    $userName = fn ($u) => trim(($u->display_name ?? '') ?: trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''))) ?: ($u->mobile ?? $u->email ?? ('#' . $u->user_id));
    $sevClass = [3 => 'bg-rose-100 text-rose-700', 2 => 'bg-amber-100 text-amber-700', 1 => 'bg-slate-100 text-slate-600'];
    $sevLabel = [3 => 'بالا', 2 => 'متوسط', 1 => 'کم'];
@endphp

@section('content')
    <div class="mx-auto max-w-6xl space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="text-base font-black text-slate-900">رصد امنیتی لانچر</h1>
            <p class="mt-1 text-sm leading-7 text-slate-500">
                نشانه‌های دستکاری که لانچر روی سیستم کاربران دیده است. هر مورد فقط یک «نشانه» است؛ قبل از تعلیق حساب
                جزئیات کاربر را بررسی کنید. تعلیق از صفحه
                <a class="font-bold text-brand-700 underline" href="{{ route('admin.launcher-devices.index') }}">دستگاه‌های لانچر</a>
                انجام می‌شود.
            </p>
            <form method="GET" action="{{ route('admin.launcher-security.watermark-lookup') }}" class="mt-4 flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-700">پیدا کردن صاحب فایل لو رفته (کد واترمارک):</span>
                <input name="code" value="{{ old('code') }}" required dir="ltr" placeholder="مثلاً 3f a2 91 0c 5e 77 d1 08"
                       class="w-72 rounded-lg border border-slate-200 px-3 py-1.5 font-mono text-xs">
                <button class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white">جستجو</button>
            </form>
            @if (session('error'))
                <p class="mt-2 text-xs font-bold text-rose-700">{{ session('error') }}</p>
            @endif
            @if (session('success'))
                <p class="mt-2 text-xs font-bold text-emerald-700">{{ session('success') }}</p>
            @endif
        </section>

        @if ($selectedUser)
            <section class="rounded-2xl border border-brand-200 bg-brand-50/40 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-black text-slate-900">رویدادهای {{ $userName($selectedUser) }}</h2>
                    <a class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-bold text-white"
                       href="{{ route('admin.launcher-devices.index', ['user_id' => $selectedUser->user_id]) }}">مدیریت دستگاه / تعلیق</a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="text-slate-500"><tr>
                            <th class="p-2">شدت</th><th class="p-2">نوع</th><th class="p-2">فایل</th><th class="p-2">SHA-256</th>
                            <th class="p-2">دستگاه</th><th class="p-2">تعداد</th><th class="p-2">اولین</th><th class="p-2">آخرین</th><th class="p-2">نسخه</th>
                        </tr></thead>
                        <tbody>
                        @forelse ($userEvents as $e)
                            <tr class="border-t border-slate-200">
                                <td class="p-2"><span class="rounded-lg px-2 py-0.5 font-bold {{ $sevClass[$e->severity] ?? '' }}">{{ $sevLabel[$e->severity] ?? $e->severity }}</span></td>
                                <td class="p-2">{{ $typeLabels[$e->event_type] ?? $e->event_type }}</td>
                                <td class="p-2 font-mono" dir="ltr">{{ $e->subject }}</td>
                                <td class="p-2 font-mono" dir="ltr" title="{{ $e->sha256 }}">{{ $e->sha256 ? substr($e->sha256, 0, 12) . '…' : '' }}</td>
                                <td class="p-2">{{ $e->device_name }}</td>
                                <td class="p-2">{{ number_format($e->occurrences) }}</td>
                                <td class="p-2">{{ jalali_date($e->first_seen_at) }}</td>
                                <td class="p-2">{{ jalali_date($e->last_seen_at) }}</td>
                                <td class="p-2" dir="ltr">{{ $e->app_version }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="p-4 text-center text-slate-500">رویدادی ثبت نشده است.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="rounded-2xl border border-rose-200 bg-rose-50/40 p-5">
            <h2 class="text-sm font-black text-slate-900">حساب‌های پرخطر (۳۰ روز اخیر)</h2>
            <p class="mt-1 text-xs leading-6 text-slate-500">امتیاز: هر نشانه با شدت بالا ۱۰، متوسط ۳، کم ۱. تکرار یک نشانه امتیاز را بالا نمی‌برد.</p>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="text-slate-500"><tr><th class="p-2">کاربر</th><th class="p-2">امتیاز</th><th class="p-2">نشانه شدید</th><th class="p-2">کل نشانه‌ها</th><th class="p-2">آخرین</th></tr></thead>
                    <tbody>
                    @forelse ($riskyUsers as $u)
                        <tr class="border-t border-rose-100">
                            <td class="p-2"><a class="font-bold text-brand-700 underline" href="{{ route('admin.launcher-security.index', ['user_id' => $u->user_id]) }}">{{ $userName($u) }}</a></td>
                            <td class="p-2 font-black">{{ $u->score }}</td>
                            <td class="p-2">{{ $u->high_count }}</td>
                            <td class="p-2">{{ $u->finding_count }}</td>
                            <td class="p-2">{{ jalali_date($u->last_seen_at) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-center text-slate-500">موردی نیست.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5">
            <h2 class="text-sm font-black text-slate-900">فایل‌های کمیاب</h2>
            <p class="mt-1 text-xs leading-6 text-slate-500">فایل‌هایی که دقیقاً همین محتوا را فقط ۱ یا ۲ حساب دارند. ابزار عمومی (مثل ReShade) روی حساب‌های زیادی دیده می‌شود؛ ابزار ساخته‌شده برای کرک معمولاً فقط روی یک حساب.</p>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="text-slate-500"><tr><th class="p-2">نوع</th><th class="p-2">فایل</th><th class="p-2">SHA-256</th><th class="p-2">تعداد حساب</th><th class="p-2">آخرین</th></tr></thead>
                    <tbody>
                    @forelse ($rareHashes as $h)
                        <tr class="border-t border-amber-100">
                            <td class="p-2"><span class="rounded-lg px-2 py-0.5 font-bold {{ $sevClass[$h->severity] ?? '' }}">{{ $typeLabels[$h->event_type] ?? $h->event_type }}</span></td>
                            <td class="p-2 font-mono" dir="ltr">{{ $h->subject }}</td>
                            <td class="p-2 font-mono" dir="ltr" title="{{ $h->sha256 }}">{{ substr($h->sha256, 0, 16) }}…</td>
                            <td class="p-2"><a class="text-brand-700 underline" href="{{ route('admin.launcher-security.index', ['user_id' => $h->sample_user_id]) }}">{{ $h->user_count }}</a></td>
                            <td class="p-2">{{ jalali_date($h->last_seen_at) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-center text-slate-500">موردی نیست.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5">
            <h2 class="text-sm font-black text-slate-900">DLLهای دیده‌شده کنار exe بازی (هنوز مجاز نشده)</h2>
            <p class="mt-1 text-xs leading-6 text-slate-500">
                پرتکرارها بالا هستند. فایلی که روی حساب‌های زیادی هست معمولاً ابزار عمومی است (ReShade، لودر پچ و ...)؛ آن را «مجاز» کنید.
                وقتی این لیست خالی یا فقط شامل موارد مشکوک شد، می‌توانید در صفحه ویرایش محصول گزینه «جلوگیری از اجرا با DLL ناشناس» را روشن کنید.
            </p>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="text-slate-500"><tr><th class="p-2">فایل</th><th class="p-2">SHA-256</th><th class="p-2">تعداد حساب</th><th class="p-2">آخرین</th><th class="p-2"></th></tr></thead>
                    <tbody>
                    @forelse ($seenGameDlls as $g)
                        <tr class="border-t border-emerald-100">
                            <td class="p-2 font-mono" dir="ltr">{{ $g->subject }}</td>
                            <td class="p-2 font-mono" dir="ltr" title="{{ $g->sha256 }}">{{ substr($g->sha256, 0, 16) }}…</td>
                            <td class="p-2"><a class="text-brand-700 underline" href="{{ route('admin.launcher-security.index', ['user_id' => $g->sample_user_id]) }}">{{ $g->user_count }}</a></td>
                            <td class="p-2">{{ jalali_date($g->last_seen_at) }}</td>
                            <td class="p-2">
                                <form method="POST" action="{{ route('admin.launcher-security.allowed-dlls.store') }}">
                                    @csrf
                                    <input type="hidden" name="sha256" value="{{ $g->sha256 }}">
                                    <input type="hidden" name="file_name" value="{{ $g->subject }}">
                                    <button class="rounded-lg bg-emerald-600 px-3 py-1 font-bold text-white">مجاز کن</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-center text-slate-500">موردی نیست.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <h3 class="mt-5 text-xs font-black text-slate-900">لیست DLLهای مجاز ({{ $allowedDlls->count() }})</h3>
            <form method="POST" action="{{ route('admin.launcher-security.allowed-dlls.store') }}" class="mt-2 flex flex-wrap gap-2">
                @csrf
                <input name="sha256" required placeholder="SHA-256 (64 حرف)" dir="ltr" class="min-w-[18rem] flex-1 rounded-lg border border-slate-200 px-3 py-1.5 font-mono text-xs">
                <input name="file_name" placeholder="نام فایل" dir="ltr" class="w-40 rounded-lg border border-slate-200 px-3 py-1.5 text-xs">
                <input name="note" placeholder="توضیح (مثلاً ReShade 6.1)" class="w-48 rounded-lg border border-slate-200 px-3 py-1.5 text-xs">
                <button class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white">افزودن دستی</button>
            </form>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <tbody>
                    @foreach ($allowedDlls as $a)
                        <tr class="border-t border-emerald-100">
                            <td class="p-2 font-mono" dir="ltr">{{ $a->file_name }}</td>
                            <td class="p-2 font-mono" dir="ltr" title="{{ $a->sha256 }}">{{ substr($a->sha256, 0, 16) }}…</td>
                            <td class="p-2">{{ $a->note }}</td>
                            <td class="p-2">{{ jalali_date($a->created_at) }}</td>
                            <td class="p-2">
                                <form method="POST" action="{{ route('admin.launcher-security.allowed-dlls.destroy', $a->id) }}"
                                      onsubmit="return confirm('از لیست مجاز حذف شود؟ اگر اجرای اجباری روشن باشد، کاربرانی که این فایل را دارند دیگر نمی‌توانند بازی را اجرا کنند.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-rose-300 px-3 py-1 font-bold text-rose-700">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5">
            <h2 class="text-sm font-black text-slate-900">دستگاه‌های ساکت</h2>
            <p class="mt-1 text-xs leading-6 text-slate-500">در ۳ روز اخیر با لانچر آنلاین بوده‌اند ولی ۷ روز است گزارش امنیتی نفرستاده‌اند، در حالی که نسخه لانچرشان گزارش می‌فرستد. شاید کاربر فقط بازی را اجرا نکرده؛ اما اگر مدام بازی می‌کند، یعنی ارسال گزارش را مسدود کرده است.</p>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="text-slate-500"><tr><th class="p-2">کاربر</th><th class="p-2">دستگاه</th><th class="p-2">نسخه</th><th class="p-2">آخرین حضور</th><th class="p-2">آخرین گزارش</th></tr></thead>
                    <tbody>
                    @forelse ($silentDevices as $d)
                        <tr class="border-t border-slate-200">
                            <td class="p-2"><a class="font-bold text-brand-700 underline" href="{{ route('admin.launcher-security.index', ['user_id' => $d->user_id]) }}">{{ $userName($d) }}</a></td>
                            <td class="p-2">{{ $d->device_name }}</td>
                            <td class="p-2" dir="ltr">{{ $d->app_version }}</td>
                            <td class="p-2">{{ jalali_date($d->last_seen_at) }}</td>
                            <td class="p-2">{{ $d->last_security_report_at ? jalali_date($d->last_security_report_at) : 'هرگز' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-center text-slate-500">موردی نیست.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
