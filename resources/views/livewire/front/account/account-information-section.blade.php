<section class="grid grid-cols-1 gap-4 xl:grid-cols-2 xl:gap-6">
    <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="text-sm font-extrabold text-slate-900 sm:text-base">اطلاعات حساب</div>
                <div class="mt-1 text-xs leading-5 text-slate-500">مشخصات اصلی حساب شما</div>
            </div>

            <div wire:loading wire:target="save,cancel" class="text-xs font-extrabold text-brand-700">
                در حال پردازش...
            </div>
        </div>

        @if($successMessage)
            <div
                class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-bold leading-6 text-emerald-800 sm:mt-5 sm:p-4 sm:text-sm sm:leading-7">
                {{ $successMessage }}
            </div>
        @endif

        @if($errorMessage)
            <div
                class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-3 text-xs font-bold leading-6 text-red-800 sm:mt-5 sm:p-4 sm:text-sm sm:leading-7">
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="mt-4 space-y-4 sm:mt-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500">نام</label>
                    <input
                        type="text"
                        wire:model.defer="first_name"
                        autocomplete="given-name"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:bg-white focus:ring-4
                        @error('first_name') border-red-300 bg-red-50 focus:border-red-300 focus:ring-red-100 @else border-slate-200 bg-slate-50 focus:border-brand-300 focus:ring-brand-100 @enderror"
                    >
                    @error('first_name')
                    <div class="mt-2 text-xs font-bold leading-5 text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500">نام خانوادگی</label>
                    <input
                        type="text"
                        wire:model.defer="last_name"
                        autocomplete="family-name"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:bg-white focus:ring-4
                        @error('last_name') border-red-300 bg-red-50 focus:border-red-300 focus:ring-red-100 @else border-slate-200 bg-slate-50 focus:border-brand-300 focus:ring-brand-100 @enderror"
                    >
                    @error('last_name')
                    <div class="mt-2 text-xs font-bold leading-5 text-red-600">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500">نام نمایشی</label>
                    <input
                        type="text"
                        wire:model.defer="display_name"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:bg-white focus:ring-4
                        @error('display_name') border-red-300 bg-red-50 focus:border-red-300 focus:ring-red-100 @else border-slate-200 bg-slate-50 focus:border-brand-300 focus:ring-brand-100 @enderror"
                    >
                    @error('display_name')
                    <div class="mt-2 text-xs font-bold leading-5 text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500">نام کاربری</label>
                    <input
                        type="text"
                        value="{{ $this->usernameLabel() }}"
                        disabled
                        class="mt-2 w-full cursor-not-allowed rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-500 outline-none"
                    >
                    <div class="mt-2 text-[11px] font-bold leading-5 text-slate-400">
                        نام کاربری قابل تغییر نیست.
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500">ایمیل</label>
                    <input
                        type="email"
                        wire:model.defer="email"
                        autocomplete="email"
                        dir="ltr"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-left text-sm font-bold text-slate-800 outline-none transition focus:bg-white focus:ring-4
                        @error('email') border-red-300 bg-red-50 focus:border-red-300 focus:ring-red-100 @else border-slate-200 bg-slate-50 focus:border-brand-300 focus:ring-brand-100 @enderror"
                    >
                    @error('email')
                    <div class="mt-2 text-xs font-bold leading-5 text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500">شماره موبایل</label>
                    <input
                        type="text"
                        wire:model.defer="mobile"
                        autocomplete="tel"
                        dir="ltr"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-left text-sm font-bold text-slate-800 outline-none transition focus:bg-white focus:ring-4
                        @error('mobile') border-red-300 bg-red-50 focus:border-red-300 focus:ring-red-100 @else border-slate-200 bg-slate-50 focus:border-brand-300 focus:ring-brand-100 @enderror"
                    >
                    @error('mobile')
                    <div class="mt-2 text-xs font-bold leading-5 text-red-600">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="pt-1 sm:pt-2">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                >
                    <span wire:loading.remove wire:target="save">ذخیره تغییرات</span>
                    <span wire:loading wire:target="save">در حال ذخیره...</span>
                </button>
            </div>
        </form>
    </div>

    <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="text-sm font-extrabold text-slate-900 sm:text-base">اطلاعات تکمیلی</div>
        <div class="mt-1 text-xs leading-5 text-slate-500">برخی وضعیت‌های مهم حساب</div>

        <div class="mt-4 space-y-3 sm:mt-5 sm:space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900">تاریخ عضویت</div>
                    <div class="text-sm font-bold text-slate-700">{{ $this->joinedAtLabel() }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold text-slate-900">نوع حساب</div>
                        <div class="mt-1 text-xs font-bold leading-5 text-slate-500">
                            با ارتقا به VIP امکانات بیشتری فعال می‌شود.
                        </div>
                    </div>

                    <span
                        class="inline-flex w-fit shrink-0 rounded-xl border px-2.5 py-1 text-[11px] font-extrabold {{ $this->accountTypeClass() }}">
                        {{ $this->accountTypeLabel() }}
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900">وضعیت ایمیل</div>

                    <span
                        class="inline-flex w-fit rounded-xl border px-2.5 py-1 text-[11px] font-extrabold {{ $this->emailVerificationClass() }}">
                        {{ $this->emailVerificationLabel() }}
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900">وضعیت موبایل</div>

                    <span
                        class="inline-flex w-fit rounded-xl border px-2.5 py-1 text-[11px] font-extrabold {{ $this->mobileVerificationClass() }}">
                        {{ $this->mobileVerificationLabel() }}
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900">وضعیت حساب</div>

                    <span
                        class="inline-flex w-fit rounded-xl border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-extrabold text-slate-700">
                        {{ $this->userStatusLabel() }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
