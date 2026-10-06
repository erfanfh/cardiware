<section class="grid grid-cols-1 gap-4 xl:grid-cols-2 xl:gap-6">
    <div
        class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">اطلاعات حساب</div>
                <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">مشخصات اصلی حساب شما</div>
            </div>

            <div wire:loading wire:target="save,cancel"
                 class="text-xs font-extrabold text-brand-700 dark:text-brand-300">
                در حال پردازش...
            </div>
        </div>

        @if($successMessage)
            <div
                class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-bold leading-6 text-emerald-800 sm:mt-5 sm:p-4 sm:text-sm sm:leading-7 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                {{ $successMessage }}
            </div>
        @endif

        @if($errorMessage)
            <div
                class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-3 text-xs font-bold leading-6 text-red-800 sm:mt-5 sm:p-4 sm:text-sm sm:leading-7 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="mt-4 space-y-4 sm:mt-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">نام</label>
                    <input
                        type="text"
                        wire:model.defer="first_name"
                        autocomplete="given-name"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold outline-none transition focus:ring-4
                            @error('first_name')
                                border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:bg-red-50 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:border-red-500/40 dark:focus:ring-red-500/20
                            @else
                                border-slate-200 bg-slate-50 text-slate-800 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                            @enderror"
                    >
                    @error('first_name')
                    <div
                        class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">نام
                        خانوادگی</label>
                    <input
                        type="text"
                        wire:model.defer="last_name"
                        autocomplete="family-name"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold outline-none transition focus:ring-4
                            @error('last_name')
                                border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:bg-red-50 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:border-red-500/40 dark:focus:ring-red-500/20
                            @else
                                border-slate-200 bg-slate-50 text-slate-800 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                            @enderror"
                    >
                    @error('last_name')
                    <div
                        class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">نام نمایشی</label>
                    <input
                        type="text"
                        wire:model.defer="display_name"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold outline-none transition focus:ring-4
                            @error('display_name')
                                border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:bg-red-50 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:border-red-500/40 dark:focus:ring-red-500/20
                            @else
                                border-slate-200 bg-slate-50 text-slate-800 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                            @enderror"
                    >
                    @error('display_name')
                    <div
                        class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">نام کاربری</label>
                    <input
                        type="text"
                        value="{{ $this->usernameLabel() }}"
                        disabled
                        class="mt-2 w-full cursor-not-allowed rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-500 outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400"
                    >
                    <div class="mt-2 text-[11px] font-bold leading-5 text-slate-400 dark:text-slate-500">
                        نام کاربری قابل تغییر نیست.
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">ایمیل</label>
                    <input
                        type="email"
                        wire:model.defer="email"
                        autocomplete="email"
                        dir="ltr"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-left text-sm font-bold outline-none transition focus:ring-4
                            @error('email')
                                border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:bg-red-50 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:border-red-500/40 dark:focus:ring-red-500/20
                            @else
                                border-slate-200 bg-slate-50 text-slate-800 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                            @enderror"
                    >
                    @error('email')
                    <div
                        class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">شماره
                        موبایل</label>
                    <input
                        type="text"
                        wire:model.defer="mobile"
                        autocomplete="tel"
                        dir="ltr"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-left text-sm font-bold outline-none transition focus:ring-4
                            @error('mobile')
                                border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:bg-red-50 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:border-red-500/40 dark:focus:ring-red-500/20
                            @else
                                border-slate-200 bg-slate-50 text-slate-800 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                            @enderror"
                    >
                    @error('mobile')
                    <div
                        class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="pt-1 sm:pt-2">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:hover:bg-brand-400 sm:w-auto"
                >
                    <span wire:loading.remove wire:target="save">ذخیره تغییرات</span>
                    <span wire:loading wire:target="save">در حال ذخیره...</span>
                </button>
            </div>
        </form>
    </div>

    <div
        class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
        <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">اطلاعات تکمیلی</div>
        <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">برخی وضعیت‌های مهم حساب</div>

        <div class="mt-4 space-y-3 sm:mt-5 sm:space-y-4">
            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">تاریخ عضویت</div>
                    <div
                        class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $this->joinedAtLabel() }}</div>
                </div>
            </div>

            <div
                @class([
                    'rounded-2xl border p-4',
                    'border-amber-200 bg-amber-50/70 dark:border-amber-500/25 dark:bg-amber-500/5' => $this->vipExpiresAt,
                    'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/70' => ! $this->vipExpiresAt,
                ])>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 [&_svg]:size-5">@include('icons.crown-03')</span>
                        <div class="min-w-0">
                            <div class="text-sm font-extrabold text-slate-900 dark:text-white">نوع حساب</div>
                            <p class="mt-1 text-xs leading-6 text-slate-600 dark:text-slate-400">
                                {{ $this->vipExpiresAt ? 'اشتراک VIP شما فعال است.' : 'هنوز اشتراک VIP فعالی ندارید.' }}
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex w-fit shrink-0 rounded-xl border px-2.5 py-1 text-[11px] font-extrabold {{ $this->accountTypeClass() }}">
                        {{ $this->accountTypeLabel() }}
                    </span>
                </div>

                @if($this->vipExpiresAt)
                    <div class="mt-3 border-t border-amber-200 pt-3 text-xs leading-6 text-amber-900 dark:border-amber-500/20 dark:text-amber-200">
                        معتبر تا <time datetime="{{ $this->vipExpiresAt->toIso8601String() }}" class="font-extrabold">{{ jalali_date($this->vipExpiresAt, 'j F Y') }} ساعت {{ jalali_date($this->vipExpiresAt, 'H:i') }}</time>
                    </div>
                @else
                    <p class="mt-3 text-xs leading-6 text-slate-600 dark:text-slate-400">با VIP به نسخه‌های منتخب محصولات بدون خرید جداگانه دسترسی بگیرید و کنار دیدگاه‌ها نشان ویژه داشته باشید.</p>
                    <a href="{{ route('vip.index') }}" class="mt-3 inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-amber-500 px-3.5 py-2.5 text-xs font-extrabold text-slate-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                        مشاهده پلن‌های VIP
                        <span aria-hidden="true">←</span>
                    </a>
                @endif
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">وضعیت ایمیل</div>

                    <span
                        class="inline-flex w-fit rounded-xl border px-2.5 py-1 text-[11px] font-extrabold {{ $this->emailVerificationClass() }}">
                            {{ $this->emailVerificationLabel() }}
                        </span>
                </div>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">وضعیت موبایل</div>

                    <span
                        class="inline-flex w-fit rounded-xl border px-2.5 py-1 text-[11px] font-extrabold {{ $this->mobileVerificationClass() }}">
                            {{ $this->mobileVerificationLabel() }}
                        </span>
                </div>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">وضعیت حساب</div>

                    <span
                        class="inline-flex w-fit rounded-xl border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-extrabold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                            {{ $this->userStatusLabel() }}
                        </span>
                </div>
            </div>
        </div>
    </div>
</section>
