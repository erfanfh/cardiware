@extends('admin.layout')

@section('title', 'مدیریت دستگاه‌های لانچر')

@section('content')
    <div class="mx-auto max-w-6xl space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex items-start gap-3">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700 [&_svg]:h-5 [&_svg]:w-5">
                        @include('icons.computer-check')
                    </div>
                    <div>
                        <h1 class="text-base font-black text-slate-900">مدیریت دستگاه‌های لانچر</h1>
                        <p class="mt-1 text-sm leading-7 text-slate-500">
                            وضعیت فعال‌سازی و انتقال را بررسی کنید و فقط پس از احراز مالکیت کاربر، دستگاه‌ها را Reset کنید.
                        </p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.launcher-devices.index') }}" class="p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-12 md:items-end">
                    <div class="md:col-span-9">
                        <x-admin.form.async-select
                            name="user_id"
                            label="کاربر"
                            placeholder="نام، موبایل، ایمیل یا شناسه کاربر را جستجو کنید..."
                            :search-url="route('admin.launcher-devices.users.search')"
                            :selected-options="$selectedUserOption"
                            :value="request('user_id')"
                            :error="$errors->first('user_id')"
                            required
                        />
                    </div>
                    <div class="md:col-span-3">
                        <x-admin.form.button type="submit" variant="dark" size="lg" block>
                            نمایش وضعیت دستگاه
                        </x-admin.form.button>
                    </div>
                </div>
            </form>
        </section>

        @if(!$selectedUser)
            @include('admin.launcher.devices.partials.list-filter', ['prefix' => 'r', 'title' => 'حساب‌هایی با Reset مکرر', 'minLabel' => 'حداقل تعداد Reset', 'count' => $resetFrequent->count()])
        @endif
        @if(!$selectedUser && isset($resetFrequent) && $resetFrequent->isNotEmpty())
            <section class="rounded-2xl border border-orange-200 bg-orange-50/40 shadow-sm">
                <div class="border-b border-orange-100 px-5 py-4">
                    <h2 class="text-sm font-black text-orange-800">حساب‌هایی با Reset مکرر</h2>
                    <p class="mt-1 text-xs font-bold leading-6 text-orange-700">
                        حساب‌هایی که در {{ $listFilters['r']['days'] }} روز اخیر حداقل {{ $listFilters['r']['min'] }} بار Reset دستگاه شده‌اند؛
                        مرتب‌شده بر اساس {{ $listFilters['r']['sort'] === 'latest' ? 'جدیدترین' : 'تعداد Reset' }}.
                    </p>
                </div>
                <div class="divide-y divide-orange-100">
                    @foreach($resetFrequent as $row)
                        @php
                            $rowName = $row->display_name ?: trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
                        @endphp
                        <a
                            href="{{ route('admin.launcher-devices.index', ['user_id' => $row->user_id]) }}"
                            class="flex flex-wrap items-center justify-between gap-3 p-4 transition hover:bg-orange-50"
                        >
                            <div>
                                <div class="text-xs font-black text-slate-900">
                                    {{ $rowName ?: 'بدون نام' }}
                                    <span class="mr-2 text-[11px] font-bold text-slate-400">#{{ $row->user_id }}</span>
                                </div>
                                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-bold text-slate-500">
                                    <span dir="ltr">{{ $row->mobile }}</span>
                                    @if($row->email)
                                        <span dir="ltr">{{ $row->email }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-[11px] font-black">
                                <span class="rounded-lg bg-orange-100 px-2 py-1 text-orange-800">
                                    {{ $row->reset_count }} Reset در ۹۰ روز
                                </span>
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600">
                                    {{ $row->reset_count_30d }} در ۳۰ روز اخیر
                                </span>
                                <span class="text-slate-400">آخرین: {{ jalali_date($row->last_reset_at) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if(!$selectedUser)
            @include('admin.launcher.devices.partials.list-filter', ['prefix' => 'w', 'title' => 'حساب‌های مشکوک به اشتراک‌گذاری', 'minLabel' => 'حداقل تلاش مسدود', 'count' => $watchlist->count()])
        @endif
        @if(!$selectedUser && $watchlist->isNotEmpty())
            <section class="rounded-2xl border border-amber-200 bg-amber-50/40 shadow-sm">
                <div class="border-b border-amber-100 px-5 py-4">
                    <h2 class="text-sm font-black text-amber-800">حساب‌های مشکوک به اشتراک‌گذاری</h2>
                    <p class="mt-1 text-xs font-bold leading-6 text-amber-700">
                        حساب‌هایی که در {{ $listFilters['w']['days'] }} روز اخیر حداقل {{ $listFilters['w']['min'] }} بار تلاش برای ورود از دستگاه سوم آن‌ها مسدود شده است؛
                        مرتب‌شده بر اساس {{ $listFilters['w']['sort'] === 'latest' ? 'جدیدترین' : 'تعداد تلاش' }}.
                    </p>
                </div>
                <div class="divide-y divide-amber-100">
                    @foreach($watchlist as $row)
                        @php
                            $rowName = $row->display_name ?: trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
                        @endphp
                        <a
                            href="{{ route('admin.launcher-devices.index', ['user_id' => $row->user_id]) }}"
                            class="flex flex-wrap items-center justify-between gap-3 p-4 transition hover:bg-amber-50"
                        >
                            <div>
                                <div class="text-xs font-black text-slate-900">
                                    {{ $rowName ?: 'بدون نام' }}
                                    <span class="mr-2 text-[11px] font-bold text-slate-400">#{{ $row->user_id }}</span>
                                </div>
                                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-bold text-slate-500">
                                    <span dir="ltr">{{ $row->mobile }}</span>
                                    @if($row->email)
                                        <span dir="ltr">{{ $row->email }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-[11px] font-black">
                                <span class="rounded-lg bg-amber-100 px-2 py-1 text-amber-800">
                                    {{ $row->attempts_count }} تلاش مسدود
                                </span>
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600">
                                    {{ $row->distinct_devices_count }} دستگاه متفاوت
                                </span>
                                <span class="text-slate-400">آخرین تلاش: {{ jalali_date($row->last_attempt_at) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if(!$selectedUser)
            @include('admin.launcher.devices.partials.list-filter', ['prefix' => 'c', 'title' => 'دستگاه‌های مشکوک به کلون شدن', 'minLabel' => 'حداقل جابه‌جایی شبکه', 'count' => $cloneSuspects->count()])
        @endif
        @if(!$selectedUser && $cloneSuspects->isNotEmpty())
            <section class="rounded-2xl border border-rose-200 bg-rose-50/40 shadow-sm">
                <div class="border-b border-rose-100 px-5 py-4">
                    <h2 class="text-sm font-black text-rose-800">دستگاه‌های مشکوک به کلون شدن</h2>
                    <p class="mt-1 text-xs font-bold leading-6 text-rose-700">
                        دستگاه‌هایی که در {{ $listFilters['c']['days'] }} روز اخیر حداقل {{ $listFilters['c']['min'] }} بار با فاصله‌ی کمتر از ۵ دقیقه بین دو شبکه‌ی متفاوت جابه‌جا شده‌اند.
                        یک دستگاه واقعی روی یک شبکه ثابت می‌ماند؛ این الگو یعنی احتمالاً دو کامپیوتر با یک شناسه‌ی یکسان نوبتی کار می‌کنند.
                        قبل از هر اقدامی حساب را باز کنید و ببینید الگو منطقی است یا نه.
                    </p>
                </div>
                <div class="divide-y divide-rose-100">
                    @foreach($cloneSuspects as $row)
                        @php
                            $rowName = $row->display_name ?: trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
                        @endphp
                        <a
                            href="{{ route('admin.launcher-devices.index', ['user_id' => $row->user_id]) }}"
                            class="flex flex-wrap items-center justify-between gap-3 p-4 transition hover:bg-rose-50"
                        >
                            <div>
                                <div class="text-xs font-black text-slate-900">
                                    {{ $rowName ?: 'بدون نام' }}
                                    <span class="mr-2 text-[11px] font-bold text-slate-400">#{{ $row->user_id }}</span>
                                </div>
                                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-bold text-slate-500">
                                    <span dir="ltr">{{ $row->mobile }}</span>
                                    @if($row->email)
                                        <span dir="ltr">{{ $row->email }}</span>
                                    @endif
                                    @if($row->device_name)
                                        <span>{{ $row->device_name }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-[11px] font-black">
                                <span class="rounded-lg bg-rose-100 px-2 py-1 text-rose-800">
                                    {{ $row->rapid_switch_count }} جابه‌جایی سریع
                                </span>
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600">
                                    {{ $row->distinct_networks_count }} شبکه‌ی متفاوت
                                </span>
                                <span class="text-slate-400">آخرین مورد: {{ jalali_date($row->last_switch_at) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($selectedUser)
            @php
                $activeDevice = $devices->first(fn($device) =>
                    $device->revoked_at === null
                    && (int)$device->device_slot === (int)$selectedUser->active_device_slot
                );
                $pendingOfflineExpiry = $devices
                    ->pluck('offline_license_expires_at')
                    ->filter()
                    ->sortDesc()
                    ->first();
            @endphp

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-500">کاربر انتخاب‌شده</div>
                        <div class="mt-1 text-base font-black text-slate-900">
                            {{ $selectedUser->display_name ?: $selectedUser->full_name }}
                            <span class="mr-2 text-xs font-bold text-slate-400">#{{ $selectedUser->getKey() }}</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-bold text-slate-600">
                        <span class="rounded-xl bg-slate-100 px-3 py-2" dir="ltr">{{ $selectedUser->mobile }}</span>
                        @if($selectedUser->email)
                            <span class="rounded-xl bg-slate-100 px-3 py-2" dir="ltr">{{ $selectedUser->email }}</span>
                        @endif
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-bold text-slate-500">دستگاه فعال</div>
                        <div class="mt-2 text-sm font-black {{ $activeDevice ? 'text-emerald-700' : 'text-slate-500' }}">
                            {{ $activeDevice?->name ?? 'دستگاهی فعال نیست' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-bold text-slate-500">اسلات فعال</div>
                        <div class="mt-2 text-sm font-black text-slate-900">
                            {{ $selectedUser->active_device_slot ? 'دستگاه ' . $selectedUser->active_device_slot : 'ثبت نشده' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-bold text-slate-500">فرصت انتقال</div>
                        <div class="mt-2 text-sm font-black {{ $selectedUser->device_transfer_used ? 'text-red-700' : 'text-emerald-700' }}">
                            {{ $selectedUser->device_transfer_used ? 'استفاده شده' : 'قابل استفاده' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-bold text-slate-500">زمان انتقال</div>
                        <div class="mt-2 text-sm font-black text-slate-900">
                            {{ $selectedUser->transferred_at ? jalali_date($selectedUser->transferred_at) : '—' }}
                        </div>
                    </div>
                </div>
            </section>

            @if($pendingOfflineExpiry && $pendingOfflineExpiry->isFuture())
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-bold leading-7 text-amber-800">
                    یکی از دستگاه‌ها تا {{ jalali_date($pendingOfflineExpiry) }} مجوز آفلاین معتبر دارد. Reset دسترسی آنلاین آن را
                    فوراً قطع می‌کند، اما مجوز آفلاین صادرشده تا پایان همین تاریخ قابل لغو از راه دور نیست؛ دستگاه جدید تا آن زمان
                    مجوز آفلاین تازه دریافت نخواهد کرد.
                </div>
            @endif

            <section class="rounded-2xl border {{ $selectedUser->isLauncherSuspended() ? 'border-red-300 bg-red-50/40' : 'border-emerald-200 bg-white' }} shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-black text-slate-900">دسترسی حساب به لانچر</h2>
                            <p class="mt-1 text-xs font-bold leading-6 text-slate-500">
                                محرومیت فقط ورود و API لانچر را مسدود می‌کند و خریدها، اسلات دستگاه و سابقه انتقال را تغییر نمی‌دهد.
                            </p>
                        </div>
                        <span class="rounded-xl px-3 py-2 text-xs font-black {{ $selectedUser->isLauncherSuspended() ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $selectedUser->isLauncherSuspended() ? 'محروم از لانچر' : 'مجاز' }}
                        </span>
                    </div>
                    @if($selectedUser->isLauncherSuspended())
                        <div class="mt-4 rounded-2xl border border-red-200 bg-white p-4 text-xs font-bold leading-7 text-red-800">
                            <div>{{ $selectedUser->launcher_suspension_reason ?: 'علتی ثبت نشده است.' }}</div>
                            <div class="mt-1 text-red-600">
                                {{ $selectedUser->launcherSuspensionIsPermanent()
                                    ? 'مدت: دائمی'
                                    : 'پایان محرومیت: ' . jalali_date($selectedUser->launcher_suspended_until) }}
                            </div>
                        </div>
                    @endif
                </div>

                @if($selectedUser->isLauncherSuspended())
                    <form method="POST" action="{{ route('admin.launcher-devices.restore') }}" class="space-y-4 p-5">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $selectedUser->getKey() }}">
                        <div>
                            <label class="mb-2 block text-xs font-black text-slate-700">دلیل رفع محرومیت</label>
                            <x-admin.form.textarea name="restore_reason" rows="3" :value="old('restore_reason')" placeholder="برای مثال: بررسی توسط پشتیبانی و رفع محدودیت" required />
                            @error('restore_reason')<p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <label class="flex items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-xs font-bold leading-6 text-emerald-800">
                            <input type="checkbox" name="confirm_restore" value="1" class="mt-1 h-4 w-4" required>
                            <span>رفع محرومیت این حساب را تأیید می‌کنم. کاربر پس از آن باید یک‌بار آنلاین وارد لانچر شود.</span>
                        </label>
                        <div class="flex justify-end">
                            <x-admin.form.button type="submit" variant="solid">رفع محرومیت لانچر</x-admin.form.button>
                        </div>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.launcher-devices.suspend') }}" class="space-y-4 p-5" onsubmit="return confirm('آیا از محروم‌کردن این حساب از لانچر مطمئن هستید؟')">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $selectedUser->getKey() }}">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-xs font-black text-slate-700">نوع محرومیت</label>
                                <select name="duration_type" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-bold" required>
                                    <option value="days" @selected(old('duration_type') === 'days')>تعداد روز مشخص</option>
                                    <option value="permanent" @selected(old('duration_type') === 'permanent')>دائمی</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-black text-slate-700">تعداد روز</label>
                                <input type="number" name="duration_days" min="1" max="3650" value="{{ old('duration_days', 10) }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-bold" inputmode="numeric">
                                @error('duration_days')<p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-black text-slate-700">علت قابل نمایش به کاربر</label>
                            <x-admin.form.textarea name="suspension_reason" rows="3" :value="old('suspension_reason')" placeholder="برای مثال: اشتراک‌گذاری حساب برخلاف قوانین استفاده از لانچر" required />
                            @error('suspension_reason')<p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <label class="flex items-start gap-3 rounded-2xl border border-red-100 bg-red-50 p-4 text-xs font-bold leading-6 text-red-800">
                            <input type="checkbox" name="confirm_suspension" value="1" class="mt-1 h-4 w-4" required>
                            <span>ابطال فوری Tokenهای لانچر و جلوگیری از ورود این حساب را تأیید می‌کنم.</span>
                        </label>
                        <div class="flex justify-end">
                            <x-admin.form.button type="submit" variant="danger">اعمال محرومیت لانچر</x-admin.form.button>
                        </div>
                    </form>
                @endif
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-black text-slate-900">۲۰ تغییر آخر دسترسی لانچر</h2>
                </div>
                @if($suspensionLogs->isEmpty())
                    <div class="px-5 py-8 text-center text-sm font-bold text-slate-500">سابقه‌ای ثبت نشده است.</div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($suspensionLogs as $log)
                            @php
                                $adminName = $log->admin_display_name ?: trim(($log->admin_first_name ?? '') . ' ' . ($log->admin_last_name ?? ''));
                            @endphp
                            <div class="p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="text-xs font-black {{ $log->action === 'suspend' ? 'text-red-700' : 'text-emerald-700' }}">
                                        {{ $log->action === 'suspend' ? 'اعمال محرومیت' : 'رفع محرومیت' }} · {{ $adminName ?: 'مدیر حذف‌شده' }}
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-400">{{ jalali_date($log->created_at) }} · {{ $log->ip_address ?: 'بدون IP' }}</div>
                                </div>
                                <p class="mt-2 text-xs font-bold leading-6 text-slate-600">{{ $log->reason ?: 'بدون توضیح' }}</p>
                                @if($log->action === 'suspend')
                                    <div class="mt-1 text-[11px] font-bold text-slate-400">
                                        {{ $log->suspended_until ? 'تا ' . jalali_date($log->suspended_until) : 'دائمی' }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-black text-slate-900">سوابق دستگاه‌ها</h2>
                </div>
                @if($devices->isEmpty())
                    <div class="px-5 py-10 text-center text-sm font-bold text-slate-500">
                        هنوز دستگاهی برای این حساب ثبت نشده است.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($devices as $device)
                            @php
                                $isActive = $device->revoked_at === null
                                    && (int)$device->device_slot === (int)$selectedUser->active_device_slot;
                            @endphp
                            <article class="p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-black text-slate-900">{{ $device->name }}</span>
                                            <span class="rounded-lg px-2 py-1 text-[11px] font-extrabold {{ $isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                                {{ $isActive ? 'فعال' : 'غیرفعال' }}
                                            </span>
                                            @if($device->device_slot)
                                                <span class="rounded-lg bg-brand-50 px-2 py-1 text-[11px] font-extrabold text-brand-700">
                                                    اسلات {{ $device->device_slot }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs font-bold text-slate-500">
                                            <span dir="ltr">ID: {{ $device->device_id }}</span>
                                            <span>آخرین مشاهده: {{ $device->last_seen_at ? jalali_date($device->last_seen_at) : '—' }}</span>
                                            <span dir="ltr">IP: {{ $device->last_ip ?: '—' }}</span>
                                            <span>نسخه لانچر: {{ $device->app_version ?: '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="text-xs font-bold text-slate-500">
                                        مجوز آفلاین:
                                        <span class="text-slate-800">
                                            {{ $device->offline_license_expires_at ? jalali_date($device->offline_license_expires_at) : 'صادر نشده' }}
                                        </span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-red-200 bg-white shadow-sm">
                <div class="border-b border-red-100 px-5 py-4">
                    <h2 class="text-sm font-black text-red-800">Reset امنیت دستگاه حساب</h2>
                    <p class="mt-1 text-xs font-bold leading-6 text-slate-500">
                        تمام Tokenها و دستگاه‌های قبلی غیرفعال می‌شوند؛ ورود بعدی به‌عنوان دستگاه اول ثبت خواهد شد.
                    </p>
                </div>
                <form
                    method="POST"
                    action="{{ route('admin.launcher-devices.reset') }}"
                    class="space-y-4 p-5"
                    onsubmit="return confirm('آیا از Reset کامل دستگاه‌های این حساب مطمئن هستید؟ این عملیات باید فقط پس از احراز مالکیت انجام شود.')"
                >
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $selectedUser->getKey() }}">

                    <div>
                        <label class="mb-2 block text-xs font-black text-slate-700">دلیل Reset</label>
                        <x-admin.form.textarea
                            name="reason"
                            rows="3"
                            :value="old('reason')"
                            placeholder="برای مثال: تعویض مادربرد و تأیید مالکیت از طریق فاکتور خرید و شماره موبایل"
                            required
                        />
                        @error('reason')
                            <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-start gap-3 rounded-2xl border border-red-100 bg-red-50 p-4 text-xs font-bold leading-6 text-red-800">
                        <input type="checkbox" name="confirm_reset" value="1" class="mt-1 h-4 w-4" required>
                        <span>مالکیت کاربر بررسی شده و غیرفعال‌شدن تمام دستگاه‌ها و استفاده دوباره از فرصت انتقال را تأیید می‌کنم.</span>
                    </label>

                    <div class="flex justify-end">
                        <x-admin.form.button type="submit" variant="danger">
                            @include('icons.arrow-reload-horizontal')
                            Reset کامل دستگاه‌های کاربر
                        </x-admin.form.button>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-black text-slate-900">۲۰ Reset آخر این حساب</h2>
                    @if(!empty($resetStats))
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] font-black">
                            <span class="rounded-lg px-2 py-1 {{ $resetStats->total >= 3 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' }}">
                                مجموع Reset: {{ $resetStats->total }}
                            </span>
                            <span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600">
                                ۳۰ روز اخیر: {{ $resetStats->last_30_days }}
                            </span>
                            @if($resetStats->last_at)
                                <span class="text-slate-400">آخرین Reset: {{ jalali_date($resetStats->last_at) }}</span>
                            @endif
                            @if($resetStats->total >= 3)
                                <span class="text-rose-700">این حساب بیش از حد معمول Reset شده — قبل از Reset بعدی مالکیت را دوباره بررسی کنید.</span>
                            @endif
                        </div>
                    @endif
                </div>
                @if($resetLogs->isEmpty())
                    <div class="px-5 py-8 text-center text-sm font-bold text-slate-500">سابقه‌ای ثبت نشده است.</div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($resetLogs as $log)
                            @php
                                $adminName = $log->admin_display_name
                                    ?: trim(($log->admin_first_name ?? '') . ' ' . ($log->admin_last_name ?? ''));
                            @endphp
                            <div class="p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="text-xs font-black text-slate-800">
                                        {{ $adminName ?: ($log->source === 'artisan' ? 'دستور سرور' : 'مدیر حذف‌شده') }}
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-400">
                                        {{ jalali_date($log->created_at) }} · {{ $log->ip_address ?: 'بدون IP' }}
                                    </div>
                                </div>
                                <p class="mt-2 text-xs font-bold leading-6 text-slate-600">{{ $log->reason }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-black text-slate-900">۲۰ تلاش مسدودشده آخر برای دستگاه سوم</h2>
                    <p class="mt-1 text-xs font-bold leading-6 text-slate-500">
                        هر بار که ورود از دستگاهی غیر از اسلات فعال (و بدون فرصت انتقال معتبر) رد شده باشد، اینجا ثبت می‌شود.
                    </p>
                </div>
                @if($transferBlockLogs->isEmpty())
                    <div class="px-5 py-8 text-center text-sm font-bold text-slate-500">سابقه‌ای ثبت نشده است.</div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($transferBlockLogs as $log)
                            <div class="p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="text-xs font-black text-red-700">
                                        {{ $log->reason === 'transfer_already_used' ? 'فرصت انتقال قبلاً استفاده شده' : 'انتقال مجاز نبود' }}
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-400">
                                        {{ jalali_date($log->created_at) }} · {{ $log->ip_address ?: 'بدون IP' }}
                                    </div>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-[11px] font-bold text-slate-500">
                                    <span>دستگاه: {{ $log->attempted_device_name ?: '—' }}</span>
                                    <span>پلتفرم: {{ $log->attempted_platform ?: '—' }}</span>
                                    <span dir="ltr">Fingerprint: {{ $log->attempted_public_key_fingerprint ?: '—' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif
    </div>
@endsection
